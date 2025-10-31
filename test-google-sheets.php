<?php
/**
 * Тестовый скрипт для проверки интеграции с Google Sheets
 * Используйте этот файл для отладки отправки данных
 */

// URL вашего Google Apps Script
$google_sheets_url = "https://script.google.com/macros/s/AKfycbzVNgTa4xGYhHh0ioKEp2qTtLW2yfdksTacJVf0GziZcpwkWU7BwHUw8_QRxOB1Prsi/exec";

// Тестовые данные
$test_data = array(
    'timestamp' => date('d.m.Y H:i:s'),
    'phone' => '+7 (999) 123-45-67',
    'contract' => 'TEST-12345',
    'additional_work' => 'Да',
    'work_descriptions' => 'Тестовая работа 1; Тестовая работа 2',
    'work_costs' => '5000; 3000',
    'sales_rating' => '5',
    'sales_feedback' => '',
    'delivery_rating' => '4',
    'delivery_feedback' => 'Хорошо, но задержка',
    'installation_rating' => '5',
    'installation_feedback' => '',
    'discounts' => 'Клей, Плинтус',
    'ip_address' => $_SERVER['REMOTE_ADDR']
);

echo "<h2>Тест отправки данных в Google Sheets</h2>";
echo "<h3>Отправляемые данные:</h3>";
echo "<pre>" . print_r($test_data, true) . "</pre>";

// Метод 1: cURL
echo "<h3>Метод 1: cURL</h3>";
if (function_exists('curl_init')) {
    $ch = curl_init($google_sheets_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($test_data))
    ));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    echo "<strong>HTTP Code:</strong> " . $httpCode . "<br>";
    echo "<strong>Результат:</strong> <pre>" . htmlspecialchars($result) . "</pre>";
    
    if ($error) {
        echo "<strong style='color: red;'>Ошибка:</strong> " . $error . "<br>";
    }
    
    echo "<strong>Подробная информация:</strong><pre>" . print_r($info, true) . "</pre>";
    
    if ($httpCode == 200 || $httpCode == 302) {
        echo "<p style='color: green; font-weight: bold;'>✅ Отправка успешна! Проверьте Google Таблицу.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Ошибка отправки. Проверьте настройки.</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ cURL не доступен на сервере</p>";
    
    // Метод 2: file_get_contents
    echo "<h3>Метод 2: file_get_contents</h3>";
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($test_data),
            'timeout' => 30,
            'follow_location' => 1
        )
    );
    
    $context = stream_context_create($options);
    $result = @file_get_contents($google_sheets_url, false, $context);
    
    if ($result !== false) {
        echo "<p style='color: green; font-weight: bold;'>✅ Отправка успешна!</p>";
        echo "<strong>Результат:</strong> <pre>" . htmlspecialchars($result) . "</pre>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Ошибка отправки</p>";
        $error = error_get_last();
        if ($error) {
            echo "<strong>Последняя ошибка PHP:</strong> <pre>" . print_r($error, true) . "</pre>";
        }
    }
}

echo "<hr>";
echo "<h3>Что проверить:</h3>";
echo "<ul>";
echo "<li>Убедитесь, что URL Google Apps Script правильный</li>";
echo "<li>Проверьте, что развертывание настроено с доступом 'Все'</li>";
echo "<li>URL должен заканчиваться на /exec (не /dev)</li>";
echo "<li>Проверьте вашу Google Таблицу - должна появиться новая строка</li>";
echo "</ul>";

echo "<p><a href='warranty.html'>← Вернуться к форме</a></p>";
?>
