<?php
/**
 * Управление пользователями
 */
define('SAAS_SYSTEM', true);
require_once '../config.php';
requireAdmin();

$success = '';
$error = '';

// Обработка апрува
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $user_id = (int)$_POST['user_id'];
    
    $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
    $stmt->execute([$user_id]);
    
    // Получение данных пользователя
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    // Отправка email пользователю
    $message = "
        <h2>Ваш аккаунт активирован!</h2>
        <p>Здравствуйте, {$user['company_name']}!</p>
        <p>Ваш аккаунт в Warranty SaaS успешно активирован.</p>
        <p><strong>Ваш поддомен:</strong> {$user['subdomain']}.".MAIN_DOMAIN."</p>
        <p><a href='".BASE_URL."/login.php' style='display: inline-block; padding: 12px 24px; background: #0071e3; color: white; text-decoration: none; border-radius: 8px; margin-top: 16px;'>Войти в систему</a></p>
    ";
    
    sendEmail($user['email'], 'Аккаунт активирован - Warranty SaaS', $message);
    
    $success = 'Пользователь одобрен и уведомлён по email';
    logActivity('user_approved', 'Одобрен пользователь: ' . $user['email']);
}

// Обработка отклонения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject'])) {
    $user_id = (int)$_POST['user_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$user_id]);
    
    // Отправка email пользователю
    $message = "
        <h2>Ваша заявка отклонена</h2>
        <p>Здравствуйте!</p>
        <p>К сожалению, ваша заявка на регистрацию в Warranty SaaS была отклонена.</p>
        <p>Если у вас есть вопросы, свяжитесь с нами: ".ADMIN_EMAIL."</p>
    ";
    
    sendEmail($user['email'], 'Заявка отклонена - Warranty SaaS', $message);
    
    $success = 'Заявка отклонена, пользователь уведомлён';
    logActivity('user_rejected', 'Отклонён пользователь: ' . $user['email']);
}

// Обработка блокировки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suspend'])) {
    $user_id = (int)$_POST['user_id'];
    
    $stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
    $stmt->execute([$user_id]);
    
    $success = 'Пользователь заблокирован';
    logActivity('user_suspended', 'Заблокирован пользователь ID: ' . $user_id);
}

// Обработка разблокировки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate'])) {
    $user_id = (int)$_POST['user_id'];
    
    $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
    $stmt->execute([$user_id]);
    
    $success = 'Пользователь разблокирован';
    logActivity('user_activated', 'Разблокирован пользователь ID: ' . $user_id);
}

// Получение всех пользователей
$stmt = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM form_submissions WHERE user_id = u.id) as submissions_count
    FROM users u
    WHERE role = 'client'
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи | Admin</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
            
            <h1 class="page-heading">Управление пользователями</h1>
            
            <div class="table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Компания</th>
                            <th>Email</th>
                            <th>Поддомен</th>
                            <th>Статус</th>
                            <th>Заявок</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><strong><?= h($user['company_name']) ?></strong></td>
                            <td><?= h($user['email']) ?></td>
                            <td>
                                <code><?= h($user['subdomain']) ?></code>
                            </td>
                            <td>
                                <?php
                                $status_badges = [
                                    'pending' => '<span class="badge badge-warning">Ожидает</span>',
                                    'approved' => '<span class="badge badge-success">Активен</span>',
                                    'rejected' => '<span class="badge badge-danger">Отклонён</span>',
                                    'suspended' => '<span class="badge badge-gray">Заблокирован</span>'
                                ];
                                echo $status_badges[$user['status']];
                                ?>
                            </td>
                            <td><?= number_format($user['submissions_count']) ?></td>
                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($user['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="approve" class="btn-action btn-success" title="Одобрить">✓</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="reject" class="btn-action btn-danger" title="Отклонить" onclick="return confirm('Отклонить?')">✕</button>
                                        </form>
                                    <?php elseif ($user['status'] === 'approved'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="suspend" class="btn-action btn-warning" title="Заблокировать" onclick="return confirm('Заблокировать?')">🔒</button>
                                        </form>
                                    <?php elseif ($user['status'] === 'suspended'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="activate" class="btn-action btn-success" title="Разблокировать">🔓</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
