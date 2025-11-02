<?php
/**
 * Страница регистрации (Версия 2.0 - без поддоменов)
 */
define('SAAS_SYSTEM', true);
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $company_name = trim($_POST['company_name'] ?? '');
    
    // Валидация
    if (empty($email) || empty($password) || empty($company_name)) {
        $error = 'Пожалуйста, заполните все поля';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 8) {
        $error = 'Пароль должен содержать минимум 8 символов';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный формат email';
    } else {
        // Проверка на существование email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Этот email уже зарегистрирован';
        } else {
            // Генерация уникального form_id (14 символов)
            $form_id = bin2hex(random_bytes(7));
            
            // Проверка уникальности form_id
            $stmt = $pdo->prepare("SELECT id FROM users WHERE form_id = ?");
            $stmt->execute([$form_id]);
            
            while ($stmt->fetch()) {
                $form_id = bin2hex(random_bytes(7));
                $stmt = $pdo->prepare("SELECT id FROM users WHERE form_id = ?");
                $stmt->execute([$form_id]);
            }
            
            // Создание аккаунта
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password, company_name, form_id, status, role) 
                VALUES (?, ?, ?, ?, 'pending', 'client')
            ");
            
            try {
                $stmt->execute([$email, $hashed_password, $company_name, $form_id]);
                
                // URL формы пользователя
                $form_url = PROTOCOL . MAIN_DOMAIN . '/form/' . $form_id;
                
                // Отправка уведомления администратору
                $admin_message = "
                    <h2>Новая заявка на регистрацию</h2>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Компания:</strong> {$company_name}</p>
                    <p><strong>URL формы:</strong> <a href='{$form_url}'>{$form_url}</a></p>
                    <p><strong>Form ID:</strong> {$form_id}</p>
                    <p><a href='" . BASE_URL . "/admin/users.php'>Перейти к управлению пользователями</a></p>
                ";
                
                sendEmail(ADMIN_EMAIL, 'Новая заявка на регистрацию', $admin_message);
                
                $success = 'Заявка отправлена! Ожидайте подтверждения администратора на email.';
                logActivity(null, 'user_registered', "Новая регистрация: {$email}");
                
            } catch (PDOException $e) {
                $error = 'Ошибка при регистрации. Попробуйте ещё раз.';
                error_log("Registration error: " . $e->getMessage());
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            padding: 48px 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1d1d1f;
            margin-bottom: 8px;
        }

        .logo p {
            color: #86868b;
            font-size: 16px;
        }

        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 15px;
        }

        .alert-error {
            background: rgba(255, 59, 48, 0.15);
            border: 1px solid #ff3b30;
            color: #ff3b30;
        }

        .alert-success {
            background: rgba(52, 199, 89, 0.15);
            border: 1px solid #34c759;
            color: #34c759;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            color: #1d1d1f;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .hint {
            font-size: 13px;
            color: #86868b;
            margin-top: 8px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .login-link {
            text-align: center;
            margin-top: 24px;
            font-size: 15px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 32px 24px;
            }

            .logo h1 {
                font-size: 28px;
            }
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

        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="form-group">
                    <label for="company_name">Название компании</label>
                    <input type="text" id="company_name" name="company_name" required autofocus value="<?= h($_POST['company_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
                    <p class="hint">На этот email придёт подтверждение</p>
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
        <?php endif; ?>

        <div class="login-link">
            <a href="login.php">Уже есть аккаунт? Войти</a>
        </div>
    </div>
</body>
</html>
