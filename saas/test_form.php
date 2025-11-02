<?php
/**
 * Тестовая страница для проверки формы
 */
define('SAAS_SYSTEM', true);
require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title>Тест формы</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 { color: #1d1d1f; margin-bottom: 24px; }
        .user-card {
            border: 2px solid #007aff;
            border-radius: 12px;
            padding: 20px;
            margin: 16px 0;
            background: #f8f9ff;
        }
        .user-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 12px;
            margin: 12px 0;
        }
        .label { font-weight: 600; }
        .value { font-family: monospace; }
        .form-link {
            display: inline-block;
            background: #007aff;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 12px;
        }
        .form-link:hover { background: #0051d5; }
        .error { color: #ff3b30; padding: 16px; background: #fff5f5; border-radius: 8px; margin: 16px 0; }
        .success { color: #34c759; padding: 16px; background: #f0fff4; border-radius: 8px; margin: 16px 0; }
        .code {
            background: #f5f5f7;
            padding: 16px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 16px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 Тест доступных форм</h1>";

// Проверка подключения к БД
try {
    $pdo->query("SELECT 1");
    echo "<div class='success'>✅ Подключение к БД работает</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Ошибка подключения к БД: " . h($e->getMessage()) . "</div>";
    echo "</div></body></html>";
    exit;
}

// Проверка существования таблицы users
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE status = 'approved' ORDER BY id ASC");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<div class='error'>❌ Нет одобренных пользователей в БД</div>";
        echo "<h3>Что делать:</h3>";
        echo "<ol>";
        echo "<li>Импортируйте <code>database_v3.sql</code> (там есть дефолтный админ)</li>";
        echo "<li>Или создайте пользователя через регистрацию</li>";
        echo "</ol>";
        
        echo "<h3>SQL для создания тестового пользователя:</h3>";
        echo "<div class='code'>";
        echo "INSERT INTO users (email, password, role, form_id, company_name, status) VALUES<br>";
        echo "('test@test.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'test123', 'Тестовая компания', 'approved');";
        echo "</div>";
        echo "<p>Пароль: <code>password</code></p>";
    } else {
        echo "<div class='success'>✅ Найдено пользователей: " . count($users) . "</div>";
        
        echo "<h2>Доступные формы:</h2>";
        
        foreach ($users as $user) {
            $form_url = BASE_URL . '/form_v3.php?id=' . $user['form_id'];
            
            echo "<div class='user-card'>";
            echo "<h3>📋 " . h($user['company_name']) . "</h3>";
            echo "<div class='user-info'>";
            echo "<div class='label'>Email:</div><div class='value'>" . h($user['email']) . "</div>";
            echo "<div class='label'>Role:</div><div class='value'>" . h($user['role']) . "</div>";
            echo "<div class='label'>Form ID:</div><div class='value'>" . h($user['form_id']) . "</div>";
            echo "<div class='label'>Status:</div><div class='value'>" . h($user['status']) . "</div>";
            echo "</div>";
            
            echo "<a href='{$form_url}' target='_blank' class='form-link'>🚀 Открыть форму</a>";
            
            echo "<div class='code' style='margin-top: 12px; font-size: 12px;'>{$form_url}</div>";
            echo "</div>";
        }
        
        // Проверка наличия таблиц v3
        echo "<hr style='margin: 32px 0; border: none; border-top: 1px solid #e5e5e5;'>";
        echo "<h2>Проверка структуры БД v3.0:</h2>";
        
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM form_labels");
            $labels_count = $stmt->fetchColumn();
            echo "<div class='success'>✅ Таблица form_labels существует ({$labels_count} записей)</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Таблица form_labels не найдена. Импортируйте database_v3.sql</div>";
        }
        
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM discount_cards");
            $cards_count = $stmt->fetchColumn();
            echo "<div class='success'>✅ Таблица discount_cards существует ({$cards_count} записей)</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Таблица discount_cards не найдена. Импортируйте database_v3.sql</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Ошибка чтения БД: " . h($e->getMessage()) . "</div>";
    echo "<p>Возможно, таблица <code>users</code> не существует. Импортируйте <code>database_v3.sql</code></p>";
}

echo "
        <hr style='margin: 32px 0; border: none; border-top: 1px solid #e5e5e5;'>
        <h3>🔧 Полезные ссылки:</h3>
        <p>
            <a href='check_db_v3.php' class='form-link'>Проверка БД</a>
            <a href='login.php' class='form-link'>Вход в систему</a>
        </p>
    </div>
</body>
</html>";
