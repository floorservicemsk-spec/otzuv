<?php
/**
 * Миграция существующих пользователей на v3.0
 * Создаёт дефолтные labels и карточки для всех пользователей
 */
define('SAAS_SYSTEM', true);
require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title>Миграция пользователей на v3.0</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            max-width: 900px;
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
        .success { color: #34c759; background: #f0fff4; padding: 12px; border-radius: 8px; margin: 8px 0; }
        .error { color: #ff3b30; background: #fff5f5; padding: 12px; border-radius: 8px; margin: 8px 0; }
        .info { color: #007aff; background: #f0f7ff; padding: 12px; border-radius: 8px; margin: 8px 0; }
        .warning { color: #ff9500; background: #fff8f0; padding: 12px; border-radius: 8px; margin: 8px 0; }
        .user-item { border-left: 4px solid #007aff; padding: 12px; margin: 12px 0; background: #f8f9ff; border-radius: 8px; }
        .btn {
            display: inline-block;
            background: #007aff;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            margin: 16px 8px 0 0;
        }
        .btn:hover { background: #0051d5; }
        .btn-danger { background: #ff3b30; }
        .btn-danger:hover { background: #d32f2f; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔄 Миграция пользователей на v3.0</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['migrate'])) {
    echo "<h2>Выполнение миграции...</h2>";
    
    try {
        // Получение всех пользователей
        $stmt = $pdo->query("SELECT id, email, company_name FROM users");
        $users = $stmt->fetchAll();
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($users as $user) {
            echo "<div class='user-item'>";
            echo "<strong>📧 {$user['email']}</strong> ({$user['company_name']})";
            
            $user_migrated = false;
            
            // Проверка и создание labels
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM form_labels WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $labels_count = $stmt->fetchColumn();
            
            if ($labels_count == 0) {
                // Создание дефолтных labels
                $default_labels = [
                    [1, 'Идентификация', 'Пожалуйста, введите номер телефона или договора, на который был сделан заказ'],
                    [2, 'Дополнительные работы, которые не вошли в договор', 'Если были дополнительные работы, которые не перечислены в договоре, укажите их здесь, чтобы включить их в гарантию. Вы оплачивали дополнительные работы, незафиксированные в договоре?'],
                    [3, 'Работа продавцов', 'Оцените по 5-балльной шкале, насколько продавец был внимателен к вашим желаниям и подбирал лучшее решение'],
                    [4, 'Работа доставки', 'Оцените по 5-балльной шкале, насколько быстро и аккуратно доставили Вашу покупку'],
                    [5, 'Работа монтажников (если заказывали монтаж в нашей компании)', 'Оцените по 5-балльной шкале, насколько качественно уложили напольное покрытие'],
                    [6, 'Забронируйте скидку на сопутствующие товары и укладку', 'Можете выбрать один или несколько вариантов']
                ];
                
                foreach ($default_labels as $label) {
                    $stmt = $pdo->prepare("INSERT INTO form_labels (user_id, step_number, step_title, step_subtitle) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$user['id'], $label[0], $label[1], $label[2]]);
                }
                
                echo "<br>✅ Созданы labels для 6 шагов";
                $user_migrated = true;
            } else {
                echo "<br>ℹ️ Labels уже существуют ({$labels_count})";
            }
            
            // Проверка и создание карточек
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM discount_cards WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $cards_count = $stmt->fetchColumn();
            
            if ($cards_count == 0) {
                // Создание дефолтных карточек
                $default_cards = [
                    [1, 'Клей', 'Скидка 10%', '/images/glue.jpg', 'Клей'],
                    [2, 'Плинтус', 'Скидка 5%', '/images/baseboard.jpg', 'Плинтус'],
                    [3, 'Подложка', 'Скидка 5%', '/images/underlay.jpg', 'Подложка'],
                    [4, 'Грунтовка', 'Скидка 10%', '/images/primer.jpg', 'Грунтовка'],
                    [5, 'Укладка', 'Скидка 30%', '/images/installation.jpg', 'Укладка']
                ];
                
                foreach ($default_cards as $card) {
                    $stmt = $pdo->prepare("INSERT INTO discount_cards (user_id, card_order, card_title, card_text, card_image, card_value, is_enabled) VALUES (?, ?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$user['id'], $card[0], $card[1], $card[2], $card[3], $card[4]]);
                }
                
                echo "<br>✅ Созданы 5 дефолтных карточек товаров";
                $user_migrated = true;
            } else {
                echo "<br>ℹ️ Карточки уже существуют ({$cards_count})";
            }
            
            if ($user_migrated) {
                $migrated++;
            } else {
                $skipped++;
            }
            
            echo "</div>";
        }
        
        echo "<div class='success'>";
        echo "<h3>✅ Миграция завершена!</h3>";
        echo "<p><strong>Обработано пользователей:</strong> " . count($users) . "</p>";
        echo "<p><strong>Мигрировано:</strong> {$migrated}</p>";
        echo "<p><strong>Пропущено (уже были данные):</strong> {$skipped}</p>";
        echo "</div>";
        
        echo "<a href='test_form.php' class='btn'>📋 Проверить формы</a>";
        echo "<a href='login.php' class='btn'>🔐 Войти в систему</a>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Ошибка миграции: " . h($e->getMessage()) . "</div>";
    }
    
} else {
    // Предварительная проверка
    try {
        // Проверка существования таблиц
        try {
            $pdo->query("SELECT 1 FROM form_labels LIMIT 1");
            echo "<div class='success'>✅ Таблица form_labels существует</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Таблица form_labels не найдена. Сначала импортируйте database_v3.sql!</div>";
            echo "<a href='check_db_v3.php' class='btn'>Проверить БД</a>";
            echo "</div></body></html>";
            exit;
        }
        
        try {
            $pdo->query("SELECT 1 FROM discount_cards LIMIT 1");
            echo "<div class='success'>✅ Таблица discount_cards существует</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Таблица discount_cards не найдена. Сначала импортируйте database_v3.sql!</div>";
            echo "<a href='check_db_v3.php' class='btn'>Проверить БД</a>";
            echo "</div></body></html>";
            exit;
        }
        
        // Получение пользователей
        $stmt = $pdo->query("SELECT id, email, company_name FROM users");
        $users = $stmt->fetchAll();
        
        if (empty($users)) {
            echo "<div class='warning'>⚠️ Пользователи не найдены в БД</div>";
            echo "<p>Сначала создайте пользователей или импортируйте database_v3.sql</p>";
            echo "</div></body></html>";
            exit;
        }
        
        echo "<div class='info'>";
        echo "<h3>ℹ️ Найдено пользователей: " . count($users) . "</h3>";
        echo "</div>";
        
        // Проверка кому нужна миграция
        $needs_migration = [];
        
        foreach ($users as $user) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM form_labels WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $labels_count = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM discount_cards WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $cards_count = $stmt->fetchColumn();
            
            if ($labels_count == 0 || $cards_count == 0) {
                $needs_migration[] = [
                    'user' => $user,
                    'labels' => $labels_count,
                    'cards' => $cards_count
                ];
            }
        }
        
        if (empty($needs_migration)) {
            echo "<div class='success'>";
            echo "<h3>✅ Все пользователи уже имеют labels и карточки!</h3>";
            echo "<p>Миграция не требуется.</p>";
            echo "</div>";
            echo "<a href='test_form.php' class='btn'>📋 Проверить формы</a>";
        } else {
            echo "<div class='warning'>";
            echo "<h3>⚠️ Пользователей требующих миграции: " . count($needs_migration) . "</h3>";
            echo "</div>";
            
            echo "<h3>Что будет создано:</h3>";
            echo "<ul>";
            
            foreach ($needs_migration as $item) {
                echo "<li><strong>{$item['user']['email']}</strong> ({$item['user']['company_name']})<br>";
                if ($item['labels'] == 0) {
                    echo "→ Будут созданы <strong>6 дефолтных labels</strong> для шагов формы<br>";
                }
                if ($item['cards'] == 0) {
                    echo "→ Будут созданы <strong>5 дефолтных карточек</strong> товаров";
                }
                echo "</li>";
            }
            
            echo "</ul>";
            
            echo "<div class='info'>";
            echo "<h3>ℹ️ Дефолтные данные:</h3>";
            echo "<p><strong>Labels (6 шагов):</strong></p>";
            echo "<ol>";
            echo "<li>Идентификация</li>";
            echo "<li>Дополнительные работы</li>";
            echo "<li>Работа продавцов (рейтинг)</li>";
            echo "<li>Работа доставки (рейтинг)</li>";
            echo "<li>Работа монтажников (рейтинг)</li>";
            echo "<li>Скидки и предложения</li>";
            echo "</ol>";
            
            echo "<p><strong>Карточки товаров (5 штук):</strong></p>";
            echo "<ol>";
            echo "<li>Клей - Скидка 10%</li>";
            echo "<li>Плинтус - Скидка 5%</li>";
            echo "<li>Подложка - Скидка 5%</li>";
            echo "<li>Грунтовка - Скидка 10%</li>";
            echo "<li>Укладка - Скидка 30%</li>";
            echo "</ol>";
            echo "</div>";
            
            echo "<form method='POST' action='' style='margin-top: 24px;'>";
            echo "<button type='submit' name='migrate' class='btn'>🚀 Запустить миграцию</button>";
            echo "</form>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Ошибка: " . h($e->getMessage()) . "</div>";
    }
}

echo "</div></body></html>";
