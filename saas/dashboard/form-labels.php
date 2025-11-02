<?php
/**
 * Редактор названий (labels) полей формы
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';
requireAuth();

$success = '';
$error = '';

// Получение текущих labels
$stmt = $pdo->prepare("SELECT * FROM form_labels WHERE user_id = ? ORDER BY step_number ASC");
$stmt->execute([$_SESSION['user_id']]);
$labels = $stmt->fetchAll();

// Индексирование labels по номеру шага
$labels_by_step = [];
foreach ($labels as $label) {
    $labels_by_step[$label['step_number']] = $label;
}

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_labels'])) {
    foreach ($_POST['labels'] as $step_number => $data) {
        $step_title = $data['title'] ?? '';
        $step_subtitle = $data['subtitle'] ?? '';
        
        // Проверка существования
        $stmt = $pdo->prepare("SELECT id FROM form_labels WHERE user_id = ? AND step_number = ?");
        $stmt->execute([$_SESSION['user_id'], $step_number]);
        $exists = $stmt->fetchColumn();
        
        if ($exists) {
            // Обновление
            $stmt = $pdo->prepare("
                UPDATE form_labels 
                SET step_title = ?, step_subtitle = ? 
                WHERE user_id = ? AND step_number = ?
            ");
            $stmt->execute([$step_title, $step_subtitle, $_SESSION['user_id'], $step_number]);
        } else {
            // Вставка
            $stmt = $pdo->prepare("
                INSERT INTO form_labels (user_id, step_number, step_title, step_subtitle) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $step_number, $step_title, $step_subtitle]);
        }
    }
    
    $success = 'Названия полей успешно сохранены!';
    logActivity('labels_update', 'Обновлены названия полей формы');
    
    // Обновление данных
    $stmt = $pdo->prepare("SELECT * FROM form_labels WHERE user_id = ? ORDER BY step_number ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $labels = $stmt->fetchAll();
    $labels_by_step = [];
    foreach ($labels as $label) {
        $labels_by_step[$label['step_number']] = $label;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Названия полей | Warranty SaaS</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .labels-section {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid var(--border-color);
        }
        
        .step-editor {
            padding: 24px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 20px;
            transition: all 0.2s ease;
        }
        
        .step-editor:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .step-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .step-number {
            width: 48px;
            height: 48px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .step-name {
            font-size: 18px;
            font-weight: 600;
            color: #1d1d1f;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1d1d1f;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }
        
        .hint-text {
            font-size: 13px;
            color: #86868b;
            margin-top: 6px;
        }
        
        .save-section {
            position: sticky;
            bottom: 24px;
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            border: 1px solid var(--border-color);
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
        }
        
        .preview-btn {
            padding: 12px 24px;
            background: #f5f5f7;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            color: #1d1d1f;
            display: inline-block;
        }
        
        .preview-btn:hover {
            background: #e8e8ed;
        }
        
        .default-values {
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-area">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= h($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <div class="page-header">
                <h1>Названия полей формы</h1>
                <p>Измените заголовки и подзаголовки для каждого шага формы</p>
            </div>
            
            <form method="POST" action="">
                <div class="labels-section">
                    <!-- Step 1 -->
                    <div class="step-editor">
                        <div class="step-header">
                            <div class="step-number">1</div>
                            <div class="step-name">Идентификация (Телефон / Договор)</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="step1_title">Заголовок шага</label>
                            <input type="text" 
                                   id="step1_title" 
                                   name="labels[1][title]" 
                                   value="<?= h($labels_by_step[1]['step_title'] ?? 'Идентификация') ?>"
                                   placeholder="Идентификация">
                        </div>
                        
                        <div class="form-group">
                            <label for="step1_subtitle">Подзаголовок (описание)</label>
                            <textarea id="step1_subtitle" 
                                      name="labels[1][subtitle]"
                                      placeholder="Пожалуйста, введите номер телефона или договора, на который был сделан заказ"><?= h($labels_by_step[1]['step_subtitle'] ?? 'Пожалуйста, введите номер телефона или договора, на который был сделан заказ') ?></textarea>
                            <div class="hint-text">Этот текст будет отображаться под заголовком</div>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="step-editor">
                        <div class="step-header">
                            <div class="step-number">2</div>
                            <div class="step-name">Дополнительные работы</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="step2_title">Заголовок шага</label>
                            <input type="text" 
                                   id="step2_title" 
                                   name="labels[2][title]" 
                                   value="<?= h($labels_by_step[2]['step_title'] ?? 'Дополнительные работы, которые не вошли в договор') ?>"
                                   placeholder="Дополнительные работы, которые не вошли в договор">
                        </div>
                        
                        <div class="form-group">
                            <label for="step2_subtitle">Подзаголовок (описание)</label>
                            <textarea id="step2_subtitle" 
                                      name="labels[2][subtitle]"><?= h($labels_by_step[2]['step_subtitle'] ?? 'Если были дополнительные работы, которые не перечислены в договоре, укажите их здесь, чтобы включить их в гарантию. Вы оплачивали дополнительные работы, незафиксированные в договоре?') ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="step-editor">
                        <div class="step-header">
                            <div class="step-number">3</div>
                            <div class="step-name">Рейтинг: Работа продавцов</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="step3_title">Заголовок шага</label>
                            <input type="text" 
                                   id="step3_title" 
                                   name="labels[3][title]" 
                                   value="<?= h($labels_by_step[3]['step_title'] ?? 'Работа продавцов') ?>"
                                   placeholder="Работа продавцов">
                        </div>
                        
                        <div class="form-group">
                            <label for="step3_subtitle">Подзаголовок (описание)</label>
                            <textarea id="step3_subtitle" 
                                      name="labels[3][subtitle]"><?= h($labels_by_step[3]['step_subtitle'] ?? 'Оцените по 5-балльной шкале, насколько продавец был внимателен к вашим желаниям и подбирал лучшее решение') ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Step 4 -->
                    <div class="step-editor">
                        <div class="step-header">
                            <div class="step-number">4</div>
                            <div class="step-name">Рейтинг: Работа доставки</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="step4_title">Заголовок шага</label>
                            <input type="text" 
                                   id="step4_title" 
                                   name="labels[4][title]" 
                                   value="<?= h($labels_by_step[4]['step_title'] ?? 'Работа доставки') ?>"
                                   placeholder="Работа доставки">
                        </div>
                        
                        <div class="form-group">
                            <label for="step4_subtitle">Подзаголовок (описание)</label>
                            <textarea id="step4_subtitle" 
                                      name="labels[4][subtitle]"><?= h($labels_by_step[4]['step_subtitle'] ?? 'Оцените по 5-балльной шкале, насколько быстро и аккуратно доставили Вашу покупку') ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Step 5 -->
                    <div class="step-editor">
                        <div class="step-header">
                            <div class="step-number">5</div>
                            <div class="step-name">Рейтинг: Работа монтажников</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="step5_title">Заголовок шага</label>
                            <input type="text" 
                                   id="step5_title" 
                                   name="labels[5][title]" 
                                   value="<?= h($labels_by_step[5]['step_title'] ?? 'Работа монтажников (если заказывали монтаж в нашей компании)') ?>"
                                   placeholder="Работа монтажников">
                        </div>
                        
                        <div class="form-group">
                            <label for="step5_subtitle">Подзаголовок (описание)</label>
                            <textarea id="step5_subtitle" 
                                      name="labels[5][subtitle]"><?= h($labels_by_step[5]['step_subtitle'] ?? 'Оцените по 5-балльной шкале, насколько качественно уложили напольное покрытие') ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Step 6 -->
                    <div class="step-editor">
                        <div class="step-header">
                            <div class="step-number">6</div>
                            <div class="step-name">Скидки и предложения</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="step6_title">Заголовок шага</label>
                            <input type="text" 
                                   id="step6_title" 
                                   name="labels[6][title]" 
                                   value="<?= h($labels_by_step[6]['step_title'] ?? 'Забронируйте скидку на сопутствующие товары и укладку') ?>"
                                   placeholder="Забронируйте скидку на сопутствующие товары и укладку">
                        </div>
                        
                        <div class="form-group">
                            <label for="step6_subtitle">Подзаголовок (описание)</label>
                            <textarea id="step6_subtitle" 
                                      name="labels[6][subtitle]"><?= h($labels_by_step[6]['step_subtitle'] ?? 'Можете выбрать один или несколько вариантов') ?></textarea>
                            <div class="hint-text">Карточки товаров можно редактировать в разделе "Карточки товаров"</div>
                        </div>
                    </div>
                </div>
                
                <div class="save-section">
                    <div>
                        <a href="discount-cards.php" class="preview-btn">→ Редактировать карточки товаров</a>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <?php 
                        $stmt = $pdo->prepare("SELECT form_id FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $form_id = $stmt->fetchColumn();
                        ?>
                        <a href="<?= BASE_URL ?>/form_v3.php?id=<?= h($form_id) ?>" target="_blank" class="preview-btn">👁️ Предпросмотр</a>
                        <button type="submit" name="save_labels" class="btn btn-primary">💾 Сохранить изменения</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
