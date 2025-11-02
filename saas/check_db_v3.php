<?php
/**
 * Проверка готовности БД для v3.0
 */
define('SAAS_SYSTEM', true);
require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title>Проверка БД v3.0</title>
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
        .check-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-bottom: 1px solid #e5e5e5;
        }
        .check-item:last-child { border-bottom: none; }
        .status { font-size: 24px; }
        .ok { color: #34c759; }
        .error { color: #ff3b30; }
        .warning { color: #ff9500; }
        .action {
            background: #007aff;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
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
        <h1>🔍 Проверка БД для Warranty SaaS v3.0</h1>";

$issues = [];
$all_ok = true;

// Проверка подключения к БД
try {
    $pdo->query("SELECT 1");
    echo "<div class='check-item'><span class='status ok'>✅</span> Подключение к БД работает</div>";
} catch (Exception $e) {
    echo "<div class='check-item'><span class='status error'>❌</span> Ошибка подключения к БД: " . h($e->getMessage()) . "</div>";
    $all_ok = false;
    $issues[] = "Проверьте настройки в config.php";
}

// Проверка существования таблицы users
try {
    $pdo->query("SELECT 1 FROM users LIMIT 1");
    echo "<div class='check-item'><span class='status ok'>✅</span> Таблица <code>users</code> существует</div>";
} catch (Exception $e) {
    echo "<div class='check-item'><span class='status error'>❌</span> Таблица <code>users</code> не найдена</div>";
    $all_ok = false;
    $issues[] = "Импортируйте database_v3.sql";
}

// Проверка существования таблицы form_labels
try {
    $pdo->query("SELECT 1 FROM form_labels LIMIT 1");
    echo "<div class='check-item'><span class='status ok'>✅</span> Таблица <code>form_labels</code> существует (v3.0)</div>";
} catch (Exception $e) {
    echo "<div class='check-item'><span class='status error'>❌</span> Таблица <code>form_labels</code> не найдена</div>";
    $all_ok = false;
    $issues[] = "Импортируйте database_v3.sql";
}

// Проверка существования таблицы discount_cards
try {
    $pdo->query("SELECT 1 FROM discount_cards LIMIT 1");
    echo "<div class='check-item'><span class='status ok'>✅</span> Таблица <code>discount_cards</code> существует (v3.0)</div>";
} catch (Exception $e) {
    echo "<div class='check-item'><span class='status error'>❌</span> Таблица <code>discount_cards</code> не найдена</div>";
    $all_ok = false;
    $issues[] = "Импортируйте database_v3.sql";
}

// Проверка существования таблицы form_design
try {
    $pdo->query("SELECT 1 FROM form_design LIMIT 1");
    echo "<div class='check-item'><span class='status ok'>✅</span> Таблица <code>form_design</code> существует</div>";
} catch (Exception $e) {
    echo "<div class='check-item'><span class='status error'>❌</span> Таблица <code>form_design</code> не найдена</div>";
    $all_ok = false;
}

// Проверка наличия пользователей
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        echo "<div class='check-item'><span class='status ok'>✅</span> Найдено пользователей: {$count}</div>";
    } else {
        echo "<div class='check-item'><span class='status warning'>⚠️</span> Пользователи не найдены</div>";
        $issues[] = "Создайте первого пользователя или импортируйте БД";
    }
} catch (Exception $e) {
    echo "<div class='check-item'><span class='status error'>❌</span> Ошибка чтения пользователей</div>";
}

// Проверка column form_id в users
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'form_id'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='check-item'><span class='status ok'>✅</span> Колонка <code>form_id</code> существует в <code>users</code></div>";
    } else {
        echo "<div class='check-item'><span class='status error'>❌</span> Колонка <code>form_id</code> не найдена в <code>users</code></div>";
        $all_ok = false;
        $issues[] = "Используется старая версия БД. Импортируйте database_v3.sql";
    }
} catch (Exception $e) {
    echo "<div class='check-item'><span class='status error'>❌</span> Ошибка проверки структуры таблицы users</div>";
}

echo "<hr style='margin: 24px 0; border: none; border-top: 1px solid #e5e5e5;'>";

if ($all_ok) {
    echo "<div class='check-item'><span class='status ok'>🎉</span> <strong>Всё готово к работе!</strong></div>";
    echo "<a href='login.php' class='action'>→ Перейти к входу</a>";
} else {
    echo "<div class='check-item'><span class='status error'>❌</span> <strong>Обнаружены проблемы</strong></div>";
    echo "<h3 style='margin-top: 24px;'>Что нужно сделать:</h3>";
    echo "<ol>";
    foreach ($issues as $issue) {
        echo "<li>" . h($issue) . "</li>";
    }
    echo "</ol>";
    
    echo "<h3>📝 Инструкция по импорту БД:</h3>";
    echo "<p><strong>Через HeidiSQL:</strong></p>";
    echo "<div class='code'>
1. Открыть HeidiSQL (Laragon → Database → HeidiSQL)
2. Выбрать базу warranty_saas
3. File → Load SQL file...
4. Открыть: C:\\laragon\\www\\warranty-saas\\database_v3.sql
5. Execute (F9)
    </div>";
    
    echo "<p><strong>Через Terminal:</strong></p>";
    echo "<div class='code'>
cd C:\\laragon\\www\\warranty-saas
mysql -u root warranty_saas < database_v3.sql
    </div>";
    
    echo "<p><strong>Через phpMyAdmin:</strong></p>";
    echo "<div class='code'>
1. Открыть http://localhost/phpmyadmin
2. Выбрать базу warranty_saas
3. Вкладка Импорт
4. Выбрать файл database_v3.sql
5. Нажать Вперёд
    </div>";
    
    echo "<a href='check_db_v3.php' class='action'>🔄 Проверить снова</a>";
}

echo "</div></body></html>";
