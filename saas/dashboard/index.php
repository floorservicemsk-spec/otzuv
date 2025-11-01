<?php
/**
 * Главная страница дашборда клиента
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';
requireAuth();

// Получение данных пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Получение статистики отправок
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total, 
           MAX(submitted_at) as last_submission 
    FROM form_submissions 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Получение настроек
$stmt = $pdo->prepare("SELECT * FROM form_design WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$design = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM form_integrations WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$integrations = $stmt->fetch();

// Формирование URL формы
$form_url = PROTOCOL . $user['subdomain'] . '.' . MAIN_DOMAIN . '/form.php';
$widget_url = PROTOCOL . $user['subdomain'] . '.' . MAIN_DOMAIN . '/widget.js';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Warranty SaaS</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <div class="welcome-section">
                <h1>Добро пожаловать, <?= h($user['company_name']) ?>! 👋</h1>
                <p>Управляйте вашими формами гарантии</p>
            </div>
            
            <!-- Статистика -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-label">Всего заявок</div>
                        <div class="stat-value"><?= number_format($stats['total']) ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-content">
                        <div class="stat-label">Последняя заявка</div>
                        <div class="stat-value">
                            <?php if ($stats['last_submission']): ?>
                                <?= date('d.m.Y H:i', strtotime($stats['last_submission'])) ?>
                            <?php else: ?>
                                Ещё нет заявок
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🔗</div>
                    <div class="stat-content">
                        <div class="stat-label">Поддомен</div>
                        <div class="stat-value"><?= h($user['subdomain']) ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-label">Статус</div>
                        <div class="stat-value">
                            <span class="badge badge-success">Активен</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Быстрый доступ -->
            <div class="quick-actions">
                <h2>Быстрые действия</h2>
                <div class="actions-grid">
                    <a href="design.php" class="action-card">
                        <div class="action-icon">🎨</div>
                        <div class="action-title">Дизайн</div>
                        <div class="action-description">Настройте логотип и цвета</div>
                    </a>
                    
                    <a href="integrations.php" class="action-card">
                        <div class="action-icon">🔌</div>
                        <div class="action-title">Интеграции</div>
                        <div class="action-description">Email, Telegram, Google Sheets</div>
                    </a>
                    
                    <a href="<?= $form_url ?>" target="_blank" class="action-card">
                        <div class="action-icon">🔗</div>
                        <div class="action-title">Открыть форму</div>
                        <div class="action-description">Посмотреть вашу форму</div>
                    </a>
                    
                    <a href="#" onclick="copyToClipboard('<?= $widget_url ?>'); return false;" class="action-card">
                        <div class="action-icon">📋</div>
                        <div class="action-title">Код виджета</div>
                        <div class="action-description">Скопировать код для сайта</div>
                    </a>
                </div>
            </div>
            
            <!-- Информация о форме -->
            <div class="form-info-section">
                <h2>Ваша форма гарантии</h2>
                <div class="info-card">
                    <div class="info-row">
                        <span class="info-label">URL формы:</span>
                        <span class="info-value">
                            <input type="text" readonly value="<?= $form_url ?>" id="form-url" class="copy-input">
                            <button onclick="copyToClipboard('form-url')" class="btn-copy">Копировать</button>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Код виджета:</span>
                        <span class="info-value">
                            <textarea readonly id="widget-code" class="copy-textarea" rows="3">&lt;script src="<?= $widget_url ?>"&gt;&lt;/script&gt;</textarea>
                            <button onclick="copyToClipboard('widget-code')" class="btn-copy">Копировать</button>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Настройки интеграций -->
            <div class="integrations-status">
                <h2>Статус интеграций</h2>
                <div class="integration-list">
                    <div class="integration-item">
                        <span class="integration-name">📧 Email</span>
                        <span class="badge <?= $integrations['email_enabled'] ? 'badge-success' : 'badge-gray' ?>">
                            <?= $integrations['email_enabled'] ? 'Включено' : 'Выключено' ?>
                        </span>
                    </div>
                    
                    <div class="integration-item">
                        <span class="integration-name">💬 Telegram</span>
                        <span class="badge <?= $integrations['telegram_enabled'] ? 'badge-success' : 'badge-gray' ?>">
                            <?= $integrations['telegram_enabled'] ? 'Включено' : 'Выключено' ?>
                        </span>
                    </div>
                    
                    <div class="integration-item">
                        <span class="integration-name">📊 Google Sheets</span>
                        <span class="badge <?= $integrations['google_sheets_enabled'] ? 'badge-success' : 'badge-gray' ?>">
                            <?= $integrations['google_sheets_enabled'] ? 'Включено' : 'Выключено' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            element.select();
            document.execCommand('copy');
            
            // Показать уведомление
            showNotification('Скопировано в буфер обмена!');
        }
        
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }
    </script>
</body>
</html>
