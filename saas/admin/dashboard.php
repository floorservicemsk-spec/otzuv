<?php
/**
 * Админ-панель - главная страница
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';
requireAdmin();

// Статистика
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'");
$total_clients = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'");
$pending_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM form_submissions");
$total_submissions = $stmt->fetchColumn();

// Последние регистрации
$stmt = $pdo->query("
    SELECT * FROM users 
    WHERE role = 'client' AND status = 'pending'
    ORDER BY created_at DESC 
    LIMIT 5
");
$pending_registrations = $stmt->fetchAll();

// Последняя активность
$stmt = $pdo->query("
    SELECT al.*, u.email, u.company_name 
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC 
    LIMIT 10
");
$recent_activity = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель | Warranty SaaS</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <div class="welcome-section">
                <h1>Панель администратора 🔐</h1>
                <p>Управление пользователями и системой</p>
            </div>
            
            <!-- Статистика -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <div class="stat-label">Всего клиентов</div>
                        <div class="stat-value"><?= $total_clients ?></div>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <div class="stat-label">Ожидают апрува</div>
                        <div class="stat-value"><?= $pending_users ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-label">Всего заявок</div>
                        <div class="stat-value"><?= number_format($total_submissions) ?></div>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-label">Система</div>
                        <div class="stat-value">Работает</div>
                    </div>
                </div>
            </div>
            
            <!-- Ожидающие апрува -->
            <?php if (count($pending_registrations) > 0): ?>
            <div class="admin-section">
                <h2>⏳ Ожидают подтверждения</h2>
                <div class="pending-list">
                    <?php foreach ($pending_registrations as $user): ?>
                    <div class="pending-item">
                        <div class="pending-info">
                            <div class="pending-name"><?= h($user['company_name']) ?></div>
                            <div class="pending-email"><?= h($user['email']) ?></div>
                            <div class="pending-subdomain">
                                <span class="badge badge-gray"><?= h($user['subdomain']) ?>.<?= MAIN_DOMAIN ?></span>
                            </div>
                            <div class="pending-date"><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></div>
                        </div>
                        <div class="pending-actions">
                            <form method="POST" action="users.php" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="approve" class="btn-approve">✓ Одобрить</button>
                            </form>
                            <form method="POST" action="users.php" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="reject" class="btn-reject" onclick="return confirm('Отклонить заявку?')">✕ Отклонить</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Последняя активность -->
            <div class="admin-section">
                <h2>📋 Последняя активность</h2>
                <div class="activity-list">
                    <?php foreach ($recent_activity as $log): ?>
                    <div class="activity-item">
                        <div class="activity-time"><?= date('d.m.Y H:i', strtotime($log['created_at'])) ?></div>
                        <div class="activity-user">
                            <?= $log['company_name'] ? h($log['company_name']) : 'Система' ?>
                            <span class="activity-email"><?= h($log['email'] ?? '') ?></span>
                        </div>
                        <div class="activity-action"><?= h($log['action']) ?></div>
                        <?php if ($log['description']): ?>
                        <div class="activity-description"><?= h($log['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
