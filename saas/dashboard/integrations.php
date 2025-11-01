<?php
/**
 * Страница настроек интеграций
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';
requireAuth();

$success = '';
$error = '';

// Получение текущих настроек
$stmt = $pdo->prepare("SELECT * FROM form_integrations WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$integrations = $stmt->fetch();

// Обработка сохранения настроек
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_integrations'])) {
    // Email
    $email_enabled = isset($_POST['email_enabled']) ? 1 : 0;
    $email_to = trim($_POST['email_to'] ?? '');
    $email_from = trim($_POST['email_from'] ?? DEFAULT_FROM_EMAIL);
    
    // Telegram
    $telegram_enabled = isset($_POST['telegram_enabled']) ? 1 : 0;
    $telegram_bot_token = trim($_POST['telegram_bot_token'] ?? '');
    $telegram_chat_id = trim($_POST['telegram_chat_id'] ?? '');
    
    // Google Sheets
    $google_sheets_enabled = isset($_POST['google_sheets_enabled']) ? 1 : 0;
    $google_sheets_url = trim($_POST['google_sheets_url'] ?? '');
    
    // Валидация
    if ($email_enabled && empty($email_to)) {
        $error = 'Укажите email для получения уведомлений';
    } elseif ($email_enabled && !filter_var($email_to, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный формат email';
    } elseif ($telegram_enabled && (empty($telegram_bot_token) || empty($telegram_chat_id))) {
        $error = 'Для Telegram укажите токен бота и chat ID';
    } elseif ($google_sheets_enabled && empty($google_sheets_url)) {
        $error = 'Для Google Sheets укажите URL скрипта';
    } else {
        // Сохранение
        $stmt = $pdo->prepare("
            UPDATE form_integrations 
            SET email_enabled = ?,
                email_to = ?,
                email_from = ?,
                telegram_enabled = ?,
                telegram_bot_token = ?,
                telegram_chat_id = ?,
                google_sheets_enabled = ?,
                google_sheets_url = ?
            WHERE user_id = ?
        ");
        
        $stmt->execute([
            $email_enabled,
            $email_to,
            $email_from,
            $telegram_enabled,
            $telegram_bot_token,
            $telegram_chat_id,
            $google_sheets_enabled,
            $google_sheets_url,
            $_SESSION['user_id']
        ]);
        
        $integrations = [
            'email_enabled' => $email_enabled,
            'email_to' => $email_to,
            'email_from' => $email_from,
            'telegram_enabled' => $telegram_enabled,
            'telegram_bot_token' => $telegram_bot_token,
            'telegram_chat_id' => $telegram_chat_id,
            'google_sheets_enabled' => $google_sheets_enabled,
            'google_sheets_url' => $google_sheets_url
        ];
        
        $success = 'Настройки интеграций успешно сохранены!';
        logActivity('integrations_update', 'Обновлены настройки интеграций');
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Интеграции | Warranty SaaS</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .integration-section {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid var(--border-color);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 51px;
            height: 31px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .3s;
            border-radius: 31px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 27px;
            width: 27px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: var(--success-color);
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(20px);
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.1);
        }
        
        .form-group input:disabled {
            background: var(--bg-primary);
            color: var(--text-secondary);
            cursor: not-allowed;
        }
        
        .hint-text {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 8px;
        }
        
        .hint-link {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .hint-link:hover {
            text-decoration: underline;
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
            margin-top: 16px;
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
        
        .integration-disabled {
            opacity: 0.5;
            pointer-events: none;
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
            
            <form method="POST" action="">
                <!-- Email -->
                <div class="integration-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <span>📧 Email уведомления</span>
                        </h2>
                        <label class="toggle-switch">
                            <input type="checkbox" name="email_enabled" <?= $integrations['email_enabled'] ? 'checked' : '' ?> onchange="toggleSection('email')">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div id="email-fields" class="<?= !$integrations['email_enabled'] ? 'integration-disabled' : '' ?>">
                        <div class="form-group">
                            <label>Email для получения уведомлений</label>
                            <input type="email" name="email_to" value="<?= h($integrations['email_to']) ?>" placeholder="your@email.com" <?= !$integrations['email_enabled'] ? 'disabled' : '' ?>>
                            <p class="hint-text">На этот email будут приходить все заявки с формы</p>
                        </div>
                        
                        <div class="form-group">
                            <label>Email отправителя (опционально)</label>
                            <input type="email" name="email_from" value="<?= h($integrations['email_from']) ?>" placeholder="noreply@yourdomain.com" <?= !$integrations['email_enabled'] ? 'disabled' : '' ?>>
                        </div>
                    </div>
                </div>
                
                <!-- Telegram -->
                <div class="integration-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <span>💬 Telegram уведомления</span>
                        </h2>
                        <label class="toggle-switch">
                            <input type="checkbox" name="telegram_enabled" <?= $integrations['telegram_enabled'] ? 'checked' : '' ?> onchange="toggleSection('telegram')">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div id="telegram-fields" class="<?= !$integrations['telegram_enabled'] ? 'integration-disabled' : '' ?>">
                        <div class="form-group">
                            <label>Токен бота</label>
                            <input type="text" name="telegram_bot_token" value="<?= h($integrations['telegram_bot_token']) ?>" placeholder="123456:ABC-DEF..." <?= !$integrations['telegram_enabled'] ? 'disabled' : '' ?>>
                            <p class="hint-text">
                                Получите токен у <a href="https://t.me/BotFather" target="_blank" class="hint-link">@BotFather</a> в Telegram.
                                <a href="../TELEGRAM_SETUP.md" target="_blank" class="hint-link">Подробная инструкция</a>
                            </p>
                        </div>
                        
                        <div class="form-group">
                            <label>Chat ID или @username</label>
                            <input type="text" name="telegram_chat_id" value="<?= h($integrations['telegram_chat_id']) ?>" placeholder="123456789 или @channel" <?= !$integrations['telegram_enabled'] ? 'disabled' : '' ?>>
                            <p class="hint-text">ID вашего чата или username канала (начинается с @)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Google Sheets -->
                <div class="integration-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <span>📊 Google Таблицы</span>
                        </h2>
                        <label class="toggle-switch">
                            <input type="checkbox" name="google_sheets_enabled" <?= $integrations['google_sheets_enabled'] ? 'checked' : '' ?> onchange="toggleSection('sheets')">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div id="sheets-fields" class="<?= !$integrations['google_sheets_enabled'] ? 'integration-disabled' : '' ?>">
                        <div class="form-group">
                            <label>URL Google Apps Script</label>
                            <input type="text" name="google_sheets_url" value="<?= h($integrations['google_sheets_url']) ?>" placeholder="https://script.google.com/macros/s/.../exec" <?= !$integrations['google_sheets_enabled'] ? 'disabled' : '' ?>>
                            <p class="hint-text">
                                URL вашего развёрнутого Web App.
                                <a href="../GOOGLE_SHEETS_SETUP.md" target="_blank" class="hint-link">Подробная инструкция</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="save_integrations" class="btn-save">
                    Сохранить все настройки
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function toggleSection(section) {
            const checkbox = event.target;
            const fields = document.getElementById(section + '-fields');
            const inputs = fields.querySelectorAll('input[type="text"], input[type="email"]');
            
            if (checkbox.checked) {
                fields.classList.remove('integration-disabled');
                inputs.forEach(input => input.disabled = false);
            } else {
                fields.classList.add('integration-disabled');
                inputs.forEach(input => input.disabled = true);
            }
        }
    </script>
</body>
</html>
