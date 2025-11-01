<?php
/**
 * Страница регистрации
 */
define('SAAS_SYSTEM', true);
require_once 'config.php';

// Если уже авторизован, перенаправляем
if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL . '/dashboard/index.php');
}

$error = '';
$success = '';

// Обработка регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $company_name = trim($_POST['company_name'] ?? '');
    $subdomain = strtolower(trim($_POST['subdomain'] ?? ''));
    
    // Валидация
    if (empty($email) || empty($password) || empty($company_name) || empty($subdomain)) {
        $error = 'Пожалуйста, заполните все поля';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 8) {
        $error = 'Пароль должен содержать минимум 8 символов';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный формат email';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $subdomain)) {
        $error = 'Поддомен может содержать только латинские буквы, цифры и дефис';
    } elseif (strlen($subdomain) < 3 || strlen($subdomain) > 20) {
        $error = 'Поддомен должен содержать от 3 до 20 символов';
    } else {
        // Проверка на существование email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Этот email уже зарегистрирован';
        } else {
            // Проверка доступности поддомена
            if (!isSubdomainAvailable($subdomain, $pdo)) {
                $error = 'Этот поддомен уже занят';
            } else {
                // Создание аккаунта
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (email, password, company_name, subdomain, status, role) 
                    VALUES (?, ?, ?, ?, 'pending', 'client')
                ");
                
                try {
                    $stmt->execute([$email, $hashed_password, $company_name, $subdomain]);
                    
                    // Отправка уведомления администратору
                    $admin_message = "
                        <h2>Новая заявка на регистрацию</h2>
                        <p><strong>Email:</strong> {$email}</p>
                        <p><strong>Компания:</strong> {$company_name}</p>
                        <p><strong>Поддомен:</strong> {$subdomain}</p>
                        <p><a href='" . BASE_URL . "/admin/users.php'>Перейти к управлению пользователями</a></p>
                    ";
                    
                    sendEmail(ADMIN_EMAIL, REGISTRATION_EMAIL_SUBJECT, $admin_message);
                    
                    logActivity('register', 'Новая регистрация: ' . $email, null);
                    
                    redirect(BASE_URL . '/login.php?registered=1');
                    
                } catch (PDOException $e) {
                    $error = 'Ошибка при создании аккаунта. Попробуйте позже.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | Warranty SaaS</title>
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
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border-radius: 20px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                0 2px 8px rgba(0, 0, 0, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            padding: 50px 40px;
            width: 100%;
            max-width: 500px;
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
        
        .subdomain-input {
            position: relative;
        }
        
        .subdomain-input input {
            padding-right: 180px;
        }
        
        .subdomain-suffix {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #86868b;
            font-size: 14px;
            pointer-events: none;
        }
        
        .hint {
            font-size: 12px;
            color: #86868b;
            margin-top: 6px;
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
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #0071e3;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: #0077ed;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>Создать аккаунт</h1>
            <p>Warranty SaaS</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus value="<?= h($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="company_name">Название компании</label>
                <input type="text" id="company_name" name="company_name" required value="<?= h($_POST['company_name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="subdomain">Поддомен</label>
                <div class="subdomain-input">
                    <input type="text" id="subdomain" name="subdomain" pattern="[a-z0-9-]+" required value="<?= h($_POST['subdomain'] ?? '') ?>">
                    <span class="subdomain-suffix">.<?= MAIN_DOMAIN ?></span>
                </div>
                <p class="hint">Только латинские буквы, цифры и дефис</p>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required minlength="8">
                <p class="hint">Минимум 8 символов</p>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Подтвердите пароль</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            
            <button type="submit" name="register" class="btn">Зарегистрироваться</button>
        </form>
        
        <div class="login-link">
            <a href="login.php">Уже есть аккаунт? Войти</a>
        </div>
    </div>
</body>
</html>
