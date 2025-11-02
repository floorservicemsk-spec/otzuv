<?php
/**
 * Страница настроек дизайна
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';
requireAuth();

$success = '';
$error = '';

// Получение текущих настроек
$stmt = $pdo->prepare("SELECT * FROM form_design WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$design = $stmt->fetch();

// Обработка загрузки логотипа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $file = $_FILES['logo'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_type = $file['type'];
        $file_size = $file['size'];
        
        $allowed_logo_types = array_merge(ALLOWED_IMAGE_TYPES, ['image/svg+xml']);
        
        if (!in_array($file_type, $allowed_logo_types)) {
            $error = 'Недопустимый тип файла. Разрешены: JPG, PNG, GIF, WEBP, SVG';
        } elseif ($file_size > MAX_FILE_SIZE) {
            $error = 'Файл слишком большой. Максимум ' . (MAX_FILE_SIZE / 1024 / 1024) . ' МБ';
        } else {
            // Создание папки если не существует
            $user_upload_dir = UPLOADS_DIR . '/' . $_SESSION['user_id'];
            if (!is_dir($user_upload_dir)) {
                mkdir($user_upload_dir, 0755, true);
            }
            
            // Генерация имени файла
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $extension;
            $filepath = $user_upload_dir . '/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Удаление старого логотипа
                if ($design['logo_url'] && file_exists($design['logo_url'])) {
                    unlink($design['logo_url']);
                }
                
                // Сохранение в БД
                $logo_url = '/uploads/' . $_SESSION['user_id'] . '/' . $filename;
                $stmt = $pdo->prepare("UPDATE form_design SET logo_url = ? WHERE user_id = ?");
                $stmt->execute([$logo_url, $_SESSION['user_id']]);
                
                $design['logo_url'] = $logo_url;
                $success = 'Логотип успешно загружен!';
                logActivity('logo_upload', 'Загружен новый логотип');
            } else {
                $error = 'Ошибка при загрузке файла';
            }
        }
    }
}

// Обработка удаления логотипа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_logo'])) {
    if ($design['logo_url']) {
        $file_path = __DIR__ . '/..' . $design['logo_url'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        $stmt = $pdo->prepare("UPDATE form_design SET logo_url = NULL WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $design['logo_url'] = null;
        $success = 'Логотип удалён';
        logActivity('logo_delete', 'Логотип удалён');
    }
}

// Обработка сохранения цветов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_colors'])) {
    $button_color = $_POST['button_color'] ?? DEFAULT_BUTTON_COLOR;
    $primary_color = $_POST['primary_color'] ?? DEFAULT_PRIMARY_COLOR;
    $bg_start = $_POST['bg_gradient_start'] ?? DEFAULT_BG_GRADIENT_START;
    $bg_middle = $_POST['bg_gradient_middle'] ?? DEFAULT_BG_GRADIENT_MIDDLE;
    $bg_end = $_POST['bg_gradient_end'] ?? DEFAULT_BG_GRADIENT_END;
    
    $stmt = $pdo->prepare("
        UPDATE form_design 
        SET button_color = ?, 
            primary_color = ?,
            background_gradient_start = ?,
            background_gradient_middle = ?,
            background_gradient_end = ?
        WHERE user_id = ?
    ");
    
    $stmt->execute([
        $button_color,
        $primary_color,
        $bg_start,
        $bg_middle,
        $bg_end,
        $_SESSION['user_id']
    ]);
    
    $design['button_color'] = $button_color;
    $design['primary_color'] = $primary_color;
    $design['background_gradient_start'] = $bg_start;
    $design['background_gradient_middle'] = $bg_middle;
    $design['background_gradient_end'] = $bg_end;
    
    $success = 'Цвета успешно сохранены!';
    logActivity('colors_update', 'Обновлены цвета дизайна');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Дизайн | Warranty SaaS</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .design-section {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid var(--border-color);
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }
        
        .logo-upload-area {
            display: flex;
            gap: 24px;
            align-items: flex-start;
        }
        
        .logo-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed var(--border-color);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary);
            overflow: hidden;
        }
        
        .logo-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .logo-preview-empty {
            font-size: 48px;
            color: var(--text-secondary);
        }
        
        .logo-controls {
            flex: 1;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        
        .btn-upload {
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-upload:hover {
            background: #0077ed;
            transform: translateY(-1px);
        }
        
        .btn-delete {
            padding: 12px 24px;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-left: 12px;
        }
        
        .btn-delete:hover {
            background: #ff453a;
            transform: translateY(-1px);
        }
        
        .color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
        }
        
        .color-picker-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .color-picker-group label {
            font-weight: 600;
            font-size: 14px;
        }
        
        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        input[type="color"] {
            width: 60px;
            height: 60px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
        }
        
        input[type="text"].color-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-family: 'SF Mono', monospace;
            font-size: 14px;
        }
        
        .btn-save {
            padding: 14px 32px;
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-save:hover {
            background: #30d158;
            transform: translateY(-1px);
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 15px;
        }
        
        .alert-success {
            background: rgba(52, 199, 89, 0.15);
            border: 1px solid var(--success-color);
            color: var(--success-color);
        }
        
        .alert-error {
            background: rgba(255, 59, 48, 0.15);
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
        }
        
        .hint-text {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= h($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <!-- Логотип -->
            <div class="design-section">
                <h2 class="section-title">🖼️ Логотип компании</h2>
                
                <div class="logo-upload-area">
                    <div class="logo-preview">
                        <?php if ($design['logo_url']): ?>
                            <img src="<?= h($design['logo_url']) ?>" alt="Логотип">
                        <?php else: ?>
                            <div class="logo-preview-empty">📷</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="logo-controls">
                        <form method="POST" enctype="multipart/form-data" id="logo-form">
                            <div class="file-input-wrapper">
                                <button type="button" class="btn-upload" onclick="document.getElementById('logo-file').click()">
                                    Загрузить логотип
                                </button>
                                <input type="file" id="logo-file" name="logo" accept="image/*" onchange="document.getElementById('logo-form').submit()">
                            </div>
                            
                            <?php if ($design['logo_url']): ?>
                                <button type="submit" name="delete_logo" class="btn-delete" onclick="return confirm('Удалить логотип?')">
                                    Удалить
                                </button>
                            <?php endif; ?>
                        </form>
                        
                        <p class="hint-text">
                            Рекомендуемый размер: 200x200px<br>
                            Форматы: JPG, PNG, GIF, WEBP<br>
                            Максимальный размер: <?= MAX_FILE_SIZE / 1024 / 1024 ?>МБ
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Цвета -->
            <div class="design-section">
                <h2 class="section-title">🎨 Цветовая схема</h2>
                
                <form method="POST" action="">
                    <div class="color-grid">
                        <div class="color-picker-group">
                            <label>Цвет кнопок</label>
                            <div class="color-picker-wrapper">
                                <input type="color" id="button_color" name="button_color" value="<?= h($design['button_color']) ?>">
                                <input type="text" class="color-input" value="<?= h($design['button_color']) ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="color-picker-group">
                            <label>Основной цвет</label>
                            <div class="color-picker-wrapper">
                                <input type="color" id="primary_color" name="primary_color" value="<?= h($design['primary_color']) ?>">
                                <input type="text" class="color-input" value="<?= h($design['primary_color']) ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="color-picker-group">
                            <label>Фон: начало градиента</label>
                            <div class="color-picker-wrapper">
                                <input type="color" id="bg_start" name="bg_gradient_start" value="<?= h($design['background_gradient_start']) ?>">
                                <input type="text" class="color-input" value="<?= h($design['background_gradient_start']) ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="color-picker-group">
                            <label>Фон: середина градиента</label>
                            <div class="color-picker-wrapper">
                                <input type="color" id="bg_middle" name="bg_gradient_middle" value="<?= h($design['background_gradient_middle']) ?>">
                                <input type="text" class="color-input" value="<?= h($design['background_gradient_middle']) ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="color-picker-group">
                            <label>Фон: конец градиента</label>
                            <div class="color-picker-wrapper">
                                <input type="color" id="bg_end" name="bg_gradient_end" value="<?= h($design['background_gradient_end']) ?>">
                                <input type="text" class="color-input" value="<?= h($design['background_gradient_end']) ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="save_colors" class="btn-save" style="margin-top: 32px;">
                        Сохранить изменения
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Синхронизация color picker с текстовым полем
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener('input', function() {
                this.nextElementSibling.value = this.value;
            });
        });
    </script>
</body>
</html>
