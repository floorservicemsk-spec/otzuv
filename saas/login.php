<?php
/**
 * Страница входа в систему
 */
define('SAAS_SYSTEM', true);
require_once 'config.php';

// Если уже авторизован, перенаправляем
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        redirect(BASE_URL . '/admin/dashboard.php');
    } else {
        redirect(BASE_URL . '/dashboard/index.php');
    }
}

$error = '';
$success = '';

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля';
    } else {
        // Проверка пользователя в БД
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Проверка статуса аккаунта
            if ($user['status'] === 'pending') {
                $error = 'Ваш аккаунт ожидает подтверждения администратором';
            } elseif ($user['status'] === 'rejected') {
                $error = 'Ваша заявка была отклонена';
            } elseif ($user['status'] === 'suspended') {
                $error = 'Ваш аккаунт заблокирован';
            } else {
                // Успешный вход
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['subdomain'] = $user['subdomain'];
                
                // Обновляем время последнего входа
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                logActivity('login', 'Успешный вход в систему');
                
                // Перенаправление
                if ($user['role'] === 'admin') {
                    redirect(BASE_URL . '/admin/dashboard.php');
                } else {
                    redirect(BASE_URL . '/dashboard/index.php');
                }
            }
        } else {
            $error = 'Неверный email или пароль';
            logActivity('login_failed', 'Неудачная попытка входа: ' . $email, null);
        }
    }
}

// Если есть параметр success (после регистрации)
if (isset($_GET['registered'])) {
    $success = 'Регистрация успешна! Ожидайте подтверждения от администратора.';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | Warranty SaaS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border-radius: 20px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                0 2px 8px rgba(0, 0, 0, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo h1 {
            font-size: 32px;
            font-weight: 600;
            color: #1d1d1f;
            letter-spacing: -0.5px;
        }
        
        .logo p {
            font-size: 14px;
            color: #86868b;
            margin-top: 8px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #1d1d1f;
            margin-bottom: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .form-group input:focus {
            background: white;
            border-color: #0071e3;
            box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: #0071e3;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn:hover {
            background: #0077ed;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(0, 113, 227, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            color: #c53030;
        }
        
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #15803d;
        }
        
        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: rgba(0, 0, 0, 0.1);
        }
        
        .divider span {
            background: rgba(255, 255, 255, 0.95);
            padding: 0 15px;
            position: relative;
            font-size: 13px;
            color: #86868b;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-link a {
            color: #0071e3;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .register-link a:hover {
            color: #0077ed;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Warranty SaaS</h1>
            <p>Управление гарантиями</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login" class="btn">Войти</button>
        </form>
        
        <div class="divider"><span>или</span></div>
        
        <div class="register-link">
            <a href="register.php">Создать аккаунт</a>
        </div>
    </div>
</body>
</html>
