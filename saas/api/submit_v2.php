<?php
/**
 * API endpoint для обработки заявок формы (Версия 2.0 - JSON данные)
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    // Получение JSON данных
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Неверный формат данных');
    }
    
    $user_id = (int)($data['user_id'] ?? 0);
    $form_id = $data['form_id'] ?? '';
    $fields = $data['fields'] ?? [];
    
    // Валидация
    if (empty($user_id) || empty($form_id) || empty($fields)) {
        throw new Exception('Заполните все обязательные поля');
    }
    
    // Получение пользователя
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND form_id = ? AND status = 'approved'");
    $stmt->execute([$user_id, $form_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Пользователь не найден');
    }
    
    // Получение настроек интеграций
    $stmt = $pdo->prepare("SELECT * FROM form_integrations WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $integrations = $stmt->fetch();
    
    // Получение информации о полях для форматирования уведомлений
    $stmt = $pdo->prepare("SELECT * FROM form_fields WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $form_fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $fields_map = [];
    foreach ($form_fields as $field) {
        $fields_map[$field['field_key']] = $field['field_label'];
    }
    
    // Сохранение в БД (JSON формат)
    $stmt = $pdo->prepare("
        INSERT INTO form_submissions 
        (user_id, form_data, ip_address, user_agent)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user_id,
        json_encode($fields, JSON_UNESCAPED_UNICODE),
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    
    $submission_id = $pdo->lastInsertId();
    
    // Форматирование данных для уведомлений
    $formatted_data = '';
    foreach ($fields as $key => $value) {
        $label = $fields_map[$key] ?? $key;
        $formatted_data .= "<p><strong>{$label}:</strong> " . htmlspecialchars($value) . "</p>\n";
    }
    
    // Email уведомление
    if ($integrations['email_enabled'] && !empty($integrations['email_to'])) {
        $message = "
            <h2>Новая заявка на активацию гарантии</h2>
            <p><strong>Компания:</strong> {$user['company_name']}</p>
            <p><strong>Дата:</strong> " . date('d.m.Y H:i:s') . "</p>
            <hr>
            {$formatted_data}
        ";
        
        sendEmail($integrations['email_to'], 'Новая заявка - ' . $user['company_name'], $message, $integrations['email_from']);
    }
    
    // Telegram уведомление
    if ($integrations['telegram_enabled'] && !empty($integrations['telegram_bot_token']) && !empty($integrations['telegram_chat_id'])) {
        $telegram_message = "🔔 *Новая заявка*\n\n";
        $telegram_message .= "*Компания:* {$user['company_name']}\n\n";
        
        foreach ($fields as $key => $value) {
            $label = $fields_map[$key] ?? $key;
            $telegram_message .= "*{$label}:* {$value}\n";
        }
        
        $telegram_message .= "\n📅 " . date('d.m.Y H:i:s');
        
        $telegram_url = "https://api.telegram.org/bot{$integrations['telegram_bot_token']}/sendMessage";
        $telegram_data = [
            'chat_id' => $integrations['telegram_chat_id'],
            'text' => $telegram_message,
            'parse_mode' => 'Markdown'
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($telegram_data),
                'timeout' => 10
            ]
        ];
        
        @file_get_contents($telegram_url, false, stream_context_create($options));
    }
    
    // Google Sheets
    if ($integrations['google_sheets_enabled'] && !empty($integrations['google_sheets_url'])) {
        $sheets_data = [
            'timestamp' => date('d.m.Y H:i:s'),
            'company' => $user['company_name']
        ];
        
        // Добавляем все поля
        foreach ($fields as $key => $value) {
            // Убираем + из телефонов для Google Sheets
            if (strpos($key, 'phone') !== false) {
                $value = str_replace('+', '', $value);
            }
            $sheets_data[$key] = $value;
        }
        
        if (function_exists('curl_init')) {
            $ch = curl_init($integrations['google_sheets_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sheets_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            
            curl_exec($ch);
            curl_close($ch);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Заявка успешно отправлена!',
        'submission_id' => $submission_id
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
