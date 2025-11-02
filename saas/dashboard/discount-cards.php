<?php
/**
 * Редактор карточек товаров/услуг (блок 6 - скидки)
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';
requireAuth();

$success = '';
$error = '';

// Получение текущих карточек
$stmt = $pdo->prepare("SELECT * FROM discount_cards WHERE user_id = ? ORDER BY card_order ASC");
$stmt->execute([$_SESSION['user_id']]);
$cards = $stmt->fetchAll();

// Обработка загрузки изображения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['card_image'])) {
    $card_id = $_POST['card_id'] ?? 0;
    $file = $_FILES['card_image'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_type = $file['type'];
        $file_size = $file['size'];
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
        
        if (!in_array($file_type, $allowed_types)) {
            $error = 'Недопустимый тип файла. Разрешены: JPG, PNG, GIF, WEBP';
        } elseif ($file_size > MAX_FILE_SIZE) {
            $error = 'Файл слишком большой. Максимум ' . (MAX_FILE_SIZE / 1024 / 1024) . ' МБ';
        } else {
            // Создание папки если не существует
            $user_upload_dir = UPLOADS_DIR . '/' . $_SESSION['user_id'] . '/cards';
            if (!is_dir($user_upload_dir)) {
                mkdir($user_upload_dir, 0755, true);
            }
            
            // Генерация имени файла
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'card_' . $card_id . '_' . time() . '.' . $extension;
            $filepath = $user_upload_dir . '/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Удаление старого изображения
                $stmt = $pdo->prepare("SELECT card_image FROM discount_cards WHERE id = ?");
                $stmt->execute([$card_id]);
                $old_image = $stmt->fetchColumn();
                
                if ($old_image && file_exists(__DIR__ . '/..' . $old_image)) {
                    unlink(__DIR__ . '/..' . $old_image);
                }
                
                // Сохранение в БД
                $image_url = '/uploads/' . $_SESSION['user_id'] . '/cards/' . $filename;
                $stmt = $pdo->prepare("UPDATE discount_cards SET card_image = ? WHERE id = ?");
                $stmt->execute([$image_url, $card_id]);
                
                $success = 'Изображение успешно загружено!';
                logActivity('card_image_upload', "Загружено изображение для карточки #{$card_id}");
            } else {
                $error = 'Ошибка при загрузке файла';
            }
        }
    }
}

// Обработка сохранения данных карточек
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_cards'])) {
    foreach ($_POST['cards'] as $card_id => $data) {
        $card_title = $data['title'] ?? '';
        $card_text = $data['text'] ?? '';
        $card_value = $data['value'] ?? '';
        $is_enabled = isset($data['enabled']) ? 1 : 0;
        
        $stmt = $pdo->prepare("
            UPDATE discount_cards 
            SET card_title = ?, card_text = ?, card_value = ?, is_enabled = ? 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$card_title, $card_text, $card_value, $is_enabled, $card_id, $_SESSION['user_id']]);
    }
    
    $success = 'Карточки успешно сохранены!';
    logActivity('cards_update', 'Обновлены карточки товаров');
    
    // Обновление данных
    $stmt = $pdo->prepare("SELECT * FROM discount_cards WHERE user_id = ? ORDER BY card_order ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $cards = $stmt->fetchAll();
}

// Обработка добавления новой карточки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_card'])) {
    $card_title = $_POST['new_card_title'] ?? 'Новая карточка';
    $card_text = $_POST['new_card_text'] ?? 'Описание';
    $card_value = $_POST['new_card_value'] ?? $card_title;
    
    // Получение максимального порядка
    $stmt = $pdo->prepare("SELECT MAX(card_order) FROM discount_cards WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $max_order = $stmt->fetchColumn() ?? 0;
    
    $stmt = $pdo->prepare("
        INSERT INTO discount_cards (user_id, card_order, card_title, card_text, card_value, is_enabled) 
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([$_SESSION['user_id'], $max_order + 1, $card_title, $card_text, $card_value]);
    
    $success = 'Новая карточка добавлена!';
    logActivity('card_add', 'Добавлена новая карточка товара');
    
    // Обновление данных
    $stmt = $pdo->prepare("SELECT * FROM discount_cards WHERE user_id = ? ORDER BY card_order ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $cards = $stmt->fetchAll();
}

// Обработка удаления карточки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_card'])) {
    $card_id = $_POST['card_id'] ?? 0;
    
    // Удаление изображения
    $stmt = $pdo->prepare("SELECT card_image FROM discount_cards WHERE id = ? AND user_id = ?");
    $stmt->execute([$card_id, $_SESSION['user_id']]);
    $image = $stmt->fetchColumn();
    
    if ($image && file_exists(__DIR__ . '/..' . $image)) {
        unlink(__DIR__ . '/..' . $image);
    }
    
    // Удаление карточки
    $stmt = $pdo->prepare("DELETE FROM discount_cards WHERE id = ? AND user_id = ?");
    $stmt->execute([$card_id, $_SESSION['user_id']]);
    
    $success = 'Карточка удалена!';
    logActivity('card_delete', "Удалена карточка #{$card_id}");
    
    // Обновление данных
    $stmt = $pdo->prepare("SELECT * FROM discount_cards WHERE user_id = ? ORDER BY card_order ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $cards = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Карточки товаров | Warranty SaaS</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .card-item {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 2px solid var(--border-color);
            transition: all 0.2s ease;
        }
        
        .card-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .card-preview {
            width: 100%;
            height: 180px;
            background: #f5f5f7;
            border-radius: 12px;
            margin-bottom: 16px;
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-preview-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #86868b;
            font-size: 48px;
        }
        
        .upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .card-preview:hover .upload-overlay {
            opacity: 1;
        }
        
        .upload-btn {
            padding: 8px 16px;
            background: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 14px;
            color: #1d1d1f;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }
        
        .btn-delete {
            flex: 1;
            padding: 8px;
            background: #ff3b30;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-delete:hover {
            background: #d32f2f;
        }
        
        .add-card-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 2px dashed var(--border-color);
            margin-bottom: 24px;
        }
        
        .add-card-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 16px;
            align-items: end;
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
        
        .hidden-file-input {
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
                <h1>Карточки товаров и услуг</h1>
                <p>Настройте предложения для шага 6 "Скидки и предложения"</p>
            </div>
            
            <!-- Добавление новой карточки -->
            <div class="add-card-section">
                <h3 style="margin-bottom: 16px;">➕ Добавить новую карточку</h3>
                <form method="POST" action="" class="add-card-form">
                    <div class="form-group" style="margin: 0;">
                        <label>Название</label>
                        <input type="text" name="new_card_title" placeholder="Клей" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label>Текст</label>
                        <input type="text" name="new_card_text" placeholder="Скидка 10%" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label>Значение</label>
                        <input type="text" name="new_card_value" placeholder="Клей" required>
                    </div>
                    <button type="submit" name="add_card" class="btn btn-primary">Добавить</button>
                </form>
            </div>
            
            <!-- Карточки -->
            <form method="POST" action="">
                <div class="cards-grid">
                    <?php foreach ($cards as $card): ?>
                    <div class="card-item">
                        <!-- Предпросмотр изображения -->
                        <div class="card-preview" style="<?= $card['card_image'] ? 'background-image: url(' . BASE_URL . h($card['card_image']) . ')' : '' ?>">
                            <?php if (!$card['card_image']): ?>
                                <div class="card-preview-empty">🖼️</div>
                            <?php endif; ?>
                            
                            <div class="upload-overlay">
                                <label for="image_<?= $card['id'] ?>" class="upload-btn">
                                    📤 Загрузить изображение
                                </label>
                            </div>
                        </div>
                        
                        <!-- Скрытая форма загрузки -->
                        <form method="POST" action="" enctype="multipart/form-data" id="upload_form_<?= $card['id'] ?>" style="display: none;">
                            <input type="hidden" name="card_id" value="<?= $card['id'] ?>">
                            <input type="file" 
                                   name="card_image" 
                                   id="image_<?= $card['id'] ?>" 
                                   accept="image/*"
                                   onchange="this.form.submit()"
                                   class="hidden-file-input">
                        </form>
                        
                        <!-- Поля редактирования -->
                        <div class="form-group">
                            <label>Название</label>
                            <input type="text" 
                                   name="cards[<?= $card['id'] ?>][title]" 
                                   value="<?= h($card['card_title']) ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label>Текст</label>
                            <input type="text" 
                                   name="cards[<?= $card['id'] ?>][text]" 
                                   value="<?= h($card['card_text']) ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label>Значение (для отправки)</label>
                            <input type="text" 
                                   name="cards[<?= $card['id'] ?>][value]" 
                                   value="<?= h($card['card_value']) ?>"
                                   required>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" 
                                   id="enabled_<?= $card['id'] ?>"
                                   name="cards[<?= $card['id'] ?>][enabled]" 
                                   <?= $card['is_enabled'] ? 'checked' : '' ?>>
                            <label for="enabled_<?= $card['id'] ?>">Показывать в форме</label>
                        </div>
                        
                        <!-- Действия -->
                        <div class="card-actions">
                            <button type="button" 
                                    class="btn-delete" 
                                    onclick="if(confirm('Удалить эту карточку?')) { deleteCard(<?= $card['id'] ?>); }">
                                🗑️ Удалить
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="save-section">
                    <div>
                        <a href="form-labels.php" class="preview-btn">← Вернуться к названиям полей</a>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <?php 
                        $stmt = $pdo->prepare("SELECT form_id FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $form_id = $stmt->fetchColumn();
                        ?>
                        <a href="<?= BASE_URL ?>/form_v3.php?id=<?= h($form_id) ?>" target="_blank" class="preview-btn">👁️ Предпросмотр</a>
                        <button type="submit" name="save_cards" class="btn btn-primary">💾 Сохранить изменения</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Скрытая форма для удаления -->
    <form method="POST" action="" id="delete-form" style="display: none;">
        <input type="hidden" name="card_id" id="delete-card-id">
        <input type="hidden" name="delete_card" value="1">
    </form>
    
    <script>
        function deleteCard(cardId) {
            document.getElementById('delete-card-id').value = cardId;
            document.getElementById('delete-form').submit();
        }
    </script>
</body>
</html>
