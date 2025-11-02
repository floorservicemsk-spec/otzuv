<?php
/**
 * API для отправки warranty формы v3.0
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не поддерживается']);
    exit;
}

// Получение данных из формы
$user_id = $_POST['user_id'] ?? 0;
$form_id = $_POST['form_id'] ?? '';

if (!$user_id || !$form_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Неверные данные']);
    exit;
}

// Проверка пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND form_id = ? AND status = 'approved'");
$stmt->execute([$user_id, $form_id]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
    exit;
}

// Сбор всех данных формы в JSON
$form_data = $_POST;
unset($form_data['user_id']);
unset($form_data['form_id']);

// Получение IP и User Agent
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

try {
    // Сохранение в БД
    $stmt = $pdo->prepare("
        INSERT INTO form_submissions (user_id, form_data, ip_address, user_agent, submitted_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $user_id,
        json_encode($form_data, JSON_UNESCAPED_UNICODE),
        $ip_address,
        $user_agent
    ]);
    
    $submission_id = $pdo->lastInsertId();
    
    // Получение настроек интеграций
    $stmt = $pdo->prepare("SELECT * FROM form_integrations WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $integrations = $stmt->fetch();
    
    // Получение labels для читаемого форматирования
    $stmt = $pdo->prepare("SELECT step_number, step_title FROM form_labels WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $labels = [];
    foreach ($stmt->fetchAll() as $label) {
        $labels[$label['step_number']] = $label['step_title'];
    }
    
    // Форматирование данных для уведомлений
    $formatted_data = '';
    
    // Телефон/Договор
    if (!empty($form_data['phone'])) {
        $formatted_data .= "Телефон: " . $form_data['phone'] . "\n";
    }
    if (!empty($form_data['contract'])) {
        $formatted_data .= "Номер договора: " . $form_data['contract'] . "\n";
    }
    
    // Дополнительные работы
    if (!empty($form_data['additional_work'])) {
        $formatted_data .= "\nДополнительные работы: " . $form_data['additional_work'] . "\n";
        
        if ($form_data['additional_work'] === 'Да' && !empty($form_data['work_description'])) {
            foreach ($form_data['work_description'] as $i => $desc) {
                $cost = $form_data['work_cost'][$i] ?? '';
                $formatted_data .= "  - {$desc}";
                if ($cost) $formatted_data .= " ({$cost} руб.)";
                $formatted_data .= "\n";
            }
        }
    }
    
    // Рейтинги
    if (!empty($form_data['sales_rating'])) {
        $formatted_data .= "\nРейтинг продавцов: " . str_repeat('★', (int)$form_data['sales_rating']) . " ({$form_data['sales_rating']}/5)\n";
        if (!empty($form_data['sales_feedback_bad'])) {
            $formatted_data .= "Комментарий: {$form_data['sales_feedback_bad']}\n";
        }
    }
    
    if (!empty($form_data['delivery_rating'])) {
        $formatted_data .= "\nРейтинг доставки: " . str_repeat('★', (int)$form_data['delivery_rating']) . " ({$form_data['delivery_rating']}/5)\n";
        if (!empty($form_data['delivery_feedback_bad'])) {
            $formatted_data .= "Комментарий: {$form_data['delivery_feedback_bad']}\n";
        }
    }
    
    if (!empty($form_data['installation_rating'])) {
        $formatted_data .= "\nРейтинг монтажников: " . str_repeat('★', (int)$form_data['installation_rating']) . " ({$form_data['installation_rating']}/5)\n";
        if (!empty($form_data['installation_feedback_bad'])) {
            $formatted_data .= "Комментарий: {$form_data['installation_feedback_bad']}\n";
        }
    }
    
    // Скидки
    if (!empty($form_data['discounts'])) {
        $formatted_data .= "\nВыбранные предложения:\n";
        foreach ($form_data['discounts'] as $discount) {
            $formatted_data .= "  - {$discount}\n";
        }
    }
    
    // === EMAIL ===
    if ($integrations && $integrations['email_enabled'] && $integrations['email_to']) {
        $to = $integrations['email_to'];
        $from = $integrations['email_from'] ?? DEFAULT_FROM_EMAIL;
        $subject = 'Новая заявка на гарантию - ' . $user['company_name'];
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f4f4f4; padding: 20px; border-radius: 8px; }
                .content { padding: 20px 0; }
                pre { background: #f9f9f9; padding: 15px; border-radius: 8px; white-space: pre-wrap; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📋 Новая заявка на активацию гарантии</h2>
                    <p><strong>Компания:</strong> {$user['company_name']}</p>
                    <p><strong>Дата:</strong> " . date('d.m.Y H:i') . "</p>
                </div>
                <div class='content'>
                    <h3>Данные заявки:</h3>
                    <pre>{$formatted_data}</pre>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: {$from}\r\n";
        
        @mail($to, $subject, $message, $headers);
    }
    
    // === TELEGRAM ===
    if ($integrations && $integrations['telegram_enabled'] && 
        $integrations['telegram_bot_token'] && $integrations['telegram_chat_id']) {
        
        $bot_token = $integrations['telegram_bot_token'];
        $chat_id = $integrations['telegram_chat_id'];
        
        $telegram_text = "🔔 *Новая заявка на гарантию*\n\n";
        $telegram_text .= "*Компания:* {$user['company_name']}\n";
        $telegram_text .= "*Дата:* " . date('d.m.Y H:i') . "\n\n";
        $telegram_text .= "```\n{$formatted_data}\n```";
        
        $telegram_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
        $telegram_data = [
            'chat_id' => $chat_id,
            'text' => $telegram_text,
            'parse_mode' => 'Markdown'
        ];
        
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($telegram_data)
            ]
        ];
        
        $context = stream_context_create($options);
        @file_get_contents($telegram_url, false, $context);
    }
    
    // === GOOGLE SHEETS ===
    if ($integrations && $integrations['google_sheets_enabled'] && $integrations['google_sheets_url']) {
        // Подготовка данных для Google Sheets
        $sheets_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'phone' => str_replace('+', '', $form_data['phone'] ?? ''), // Убираем + для Google Sheets
            'contract' => $form_data['contract'] ?? '',
            'additional_work' => $form_data['additional_work'] ?? '',
            'sales_rating' => $form_data['sales_rating'] ?? '',
            'delivery_rating' => $form_data['delivery_rating'] ?? '',
            'installation_rating' => $form_data['installation_rating'] ?? '',
            'discounts' => !empty($form_data['discounts']) ? implode(', ', $form_data['discounts']) : ''
        ];
        
        // Отправка через cURL
        $ch = curl_init($integrations['google_sheets_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sheets_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Не следовать редиректам
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Google Apps Script возвращает 302, это нормально
        if ($http_code != 200 && $http_code != 302) {
            error_log("Google Sheets error: HTTP {$http_code}");
        }
    }
    
    // Успешный ответ
    echo json_encode([
        'success' => true,
        'message' => 'Форма успешно отправлена',
        'submission_id' => $submission_id
    ]);
    
} catch (Exception $e) {
    error_log('Form submission error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при сохранении данных'
    ]);
}
