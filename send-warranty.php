<?php
// Настройки почты
$to_email = "your-email@example.com"; // ЗАМЕНИТЕ НА ВАШУ ПОЧТУ!
$subject = "Новая активация гарантии";

// Проверка метода запроса
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    die("Метод не разрешен");
}

// Получение данных из формы
$phone = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '';
$contract = isset($_POST['contract']) ? htmlspecialchars($_POST['contract']) : '';
$additional_work = isset($_POST['additional_work']) ? htmlspecialchars($_POST['additional_work']) : '';
$sales_rating = isset($_POST['sales_rating']) ? htmlspecialchars($_POST['sales_rating']) : '';
$delivery_rating = isset($_POST['delivery_rating']) ? htmlspecialchars($_POST['delivery_rating']) : '';
$installation_rating = isset($_POST['installation_rating']) ? htmlspecialchars($_POST['installation_rating']) : '';
$sales_feedback_bad = isset($_POST['sales_feedback_bad']) ? htmlspecialchars($_POST['sales_feedback_bad']) : '';
$delivery_feedback_bad = isset($_POST['delivery_feedback_bad']) ? htmlspecialchars($_POST['delivery_feedback_bad']) : '';
$installation_feedback_bad = isset($_POST['installation_feedback_bad']) ? htmlspecialchars($_POST['installation_feedback_bad']) : '';

// Дополнительные работы
$work_descriptions = isset($_POST['work_description']) ? $_POST['work_description'] : array();
$work_costs = isset($_POST['work_cost']) ? $_POST['work_cost'] : array();

// Скидки
$discounts = isset($_POST['discounts']) ? $_POST['discounts'] : array();

// Согласие
$consent = isset($_POST['consent']) ? 'Да' : 'Нет';

// Формирование HTML письма
$message = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        h2 { color: #BF081A; border-bottom: 2px solid #2f6f30; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px; }
        .section h3 { color: #2f6f30; margin-top: 0; }
        .field { margin: 10px 0; }
        .field strong { color: #555; }
        .rating { font-size: 20px; color: #FFD700; }
        .discounts-list { list-style: none; padding: 0; }
        .discounts-list li { padding: 5px 0; padding-left: 20px; position: relative; }
        .discounts-list li:before { content: '✓'; position: absolute; left: 0; color: #2f6f30; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>📋 Новая активация гарантийного талона</h2>
        
        <div class='section'>
            <h3>1. Идентификация</h3>
            <div class='field'><strong>Телефон:</strong> " . ($phone ?: 'Не указан') . "</div>
            <div class='field'><strong>Номер договора:</strong> " . ($contract ?: 'Не указан') . "</div>
        </div>
        
        <div class='section'>
            <h3>2. Дополнительные работы</h3>
            <div class='field'><strong>Были дополнительные работы:</strong> " . ($additional_work ?: 'Не указано') . "</div>";

if ($additional_work === 'Да' && !empty($work_descriptions)) {
    $message .= "<div class='field'><strong>Список работ:</strong><ul>";
    foreach ($work_descriptions as $index => $desc) {
        $cost = isset($work_costs[$index]) ? $work_costs[$index] : 'Не указана';
        if (!empty($desc)) {
            $message .= "<li>" . htmlspecialchars($desc) . " - <strong>" . htmlspecialchars($cost) . " руб.</strong></li>";
        }
    }
    $message .= "</ul></div>";
}

$message .= "
        </div>
        
        <div class='section'>
            <h3>3. Оценка работы продавцов</h3>
            <div class='field'><strong>Рейтинг:</strong> <span class='rating'>" . str_repeat('★', $sales_rating) . str_repeat('☆', 5 - $sales_rating) . "</span> (" . $sales_rating . "/5)</div>";

if (!empty($sales_feedback_bad)) {
    $message .= "<div class='field'><strong>Комментарий:</strong> " . nl2br($sales_feedback_bad) . "</div>";
}

$message .= "
        </div>
        
        <div class='section'>
            <h3>4. Оценка работы доставки</h3>
            <div class='field'><strong>Рейтинг:</strong> <span class='rating'>" . str_repeat('★', $delivery_rating) . str_repeat('☆', 5 - $delivery_rating) . "</span> (" . $delivery_rating . "/5)</div>";

if (!empty($delivery_feedback_bad)) {
    $message .= "<div class='field'><strong>Комментарий:</strong> " . nl2br($delivery_feedback_bad) . "</div>";
}

$message .= "
        </div>
        
        <div class='section'>
            <h3>5. Оценка работы монтажников</h3>
            <div class='field'><strong>Рейтинг:</strong> <span class='rating'>" . str_repeat('★', $installation_rating) . str_repeat('☆', 5 - $installation_rating) . "</span> (" . $installation_rating . "/5)</div>";

if (!empty($installation_feedback_bad)) {
    $message .= "<div class='field'><strong>Комментарий:</strong> " . nl2br($installation_feedback_bad) . "</div>";
}

$message .= "
        </div>
        
        <div class='section'>
            <h3>6. Забронированные скидки</h3>";

if (!empty($discounts)) {
    $message .= "<ul class='discounts-list'>";
    foreach ($discounts as $discount) {
        $message .= "<li>" . htmlspecialchars($discount) . "</li>";
    }
    $message .= "</ul>";
} else {
    $message .= "<div class='field'>Скидки не выбраны</div>";
}

$message .= "
        </div>
        
        <div class='section'>
            <h3>Согласие на обработку данных</h3>
            <div class='field'><strong>Статус:</strong> " . $consent . "</div>
        </div>
        
        <div style='margin-top: 30px; padding: 15px; background: #f0f0f0; border-radius: 8px; font-size: 12px; color: #666;'>
            <strong>Дата и время:</strong> " . date('d.m.Y H:i:s') . "<br>
            <strong>IP-адрес:</strong> " . $_SERVER['REMOTE_ADDR'] . "
        </div>
    </div>
</body>
</html>
";

// Заголовки для HTML письма
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Форма гарантии <noreply@yourdomain.com>" . "\r\n";
$headers .= "Reply-To: " . ($phone ?: 'noreply@yourdomain.com') . "\r\n";

// Отправка письма
if (mail($to_email, $subject, $message, $headers)) {
    // Успешная отправка
    echo json_encode([
        'success' => true,
        'message' => 'Гарантия успешно активирована! Письмо отправлено.'
    ]);
} else {
    // Ошибка отправки
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при отправке письма. Попробуйте позже.'
    ]);
}
?>
