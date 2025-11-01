<?php
/**
 * API endpoint для обработки заявок формы
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
    // Получение данных
    $user_id = (int)($_POST['user_id'] ?? 0);
    $phone = trim($_POST['phone'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sales_rating = (int)($_POST['sales_rating'] ?? 0);
    $delivery_rating = (int)($_POST['delivery_rating'] ?? 0);
    $installation_rating = (int)($_POST['installation_rating'] ?? 0);
    $consent = isset($_POST['consent']) ? 1 : 0;
    
    // Валидация
    if (empty($user_id) || empty($phone) || empty($name) || empty($email)) {
        throw new Exception('Заполните все обязательные поля');
    }
    
    if (!$consent) {
        throw new Exception('Необходимо согласие на обработку данных');
    }
    
    // Получение пользователя
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'approved'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Пользователь не найден');
    }
    
    // Получение настроек интеграций
    $stmt = $pdo->prepare("SELECT * FROM form_integrations WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $integrations = $stmt->fetch();
    
    // Сохранение в БД
    $stmt = $pdo->prepare("
        INSERT INTO form_submissions 
        (user_id, phone, name, email, sales_rating, delivery_rating, installation_rating)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user_id,
        $phone,
        $name,
        $email,
        $sales_rating,
        $delivery_rating,
        $installation_rating
    ]);
    
    $submission_id = $pdo->lastInsertId();
    
    // Email уведомление
    if ($integrations['email_enabled'] && !empty($integrations['email_to'])) {
        $message = "
            <h2>Новая заявка на активацию гарантии</h2>
            <p><strong>Телефон:</strong> $phone</p>
            <p><strong>Имя:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Оценка продаж:</strong> $sales_rating/5</p>
            <p><strong>Оценка доставки:</strong> $delivery_rating/5</p>
            <p><strong>Оценка монтажа:</strong> $installation_rating/5</p>
            <p><strong>Дата:</strong> " . date('d.m.Y H:i:s') . "</p>
        ";
        
        sendEmail($integrations['email_to'], 'Новая заявка - ' . $user['company_name'], $message, $integrations['email_from']);
    }
    
    // Telegram уведомление
    if ($integrations['telegram_enabled'] && !empty($integrations['telegram_bot_token']) && !empty($integrations['telegram_chat_id'])) {
        $telegram_message = "🔔 *Новая заявка*\n\n";
        $telegram_message .= "📞 *Телефон:* $phone\n";
        $telegram_message .= "👤 *Имя:* $name\n";
        $telegram_message .= "📧 *Email:* $email\n\n";
        $telegram_message .= "⭐ *Продажи:* $sales_rating/5\n";
        $telegram_message .= "⭐ *Доставка:* $delivery_rating/5\n";
        $telegram_message .= "⭐ *Монтаж:* $installation_rating/5\n\n";
        $telegram_message .= "📅 " . date('d.m.Y H:i:s');
        
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
            'phone' => str_replace('+', '', $phone),
            'name' => $name,
            'email' => $email,
            'sales_rating' => $sales_rating,
            'delivery_rating' => $delivery_rating,
            'installation_rating' => $installation_rating
        ];
        
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
        'message' => 'Гарантия успешно активирована!'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
