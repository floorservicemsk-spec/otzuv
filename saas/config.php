<?php
/**
 * Конфигурация SaaS сервиса
 * Warranty Management System
 */

// Предотвращение прямого доступа
if (!defined('SAAS_SYSTEM')) {
    die('Direct access not permitted');
}

// ==================== НАСТРОЙКИ БАЗЫ ДАННЫХ ====================
define('DB_HOST', 'localhost');
define('DB_NAME', 'warranty_saas');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ==================== НАСТРОЙКИ ДОМЕНА ====================
// Основной домен (без www)
define('MAIN_DOMAIN', 'yourservice.com');

// Поддерживаемые протоколы
define('USE_HTTPS', true);
define('PROTOCOL', USE_HTTPS ? 'https://' : 'http://');

// URL к корню сайта
define('BASE_URL', PROTOCOL . MAIN_DOMAIN);

// Папка с загрузками
define('UPLOADS_DIR', __DIR__ . '/uploads');
define('UPLOADS_URL', BASE_URL . '/uploads');

// ==================== НАСТРОЙКИ БЕЗОПАСНОСТИ ====================
// Секретный ключ для шифрования (ОБЯЗАТЕЛЬНО ИЗМЕНИТЕ!)
define('SECRET_KEY', 'your-secret-key-here-change-this-in-production');

// Соль для паролей (ОБЯЗАТЕЛЬНО ИЗМЕНИТЕ!)
define('PASSWORD_SALT', 'your-password-salt-change-this');

// Длительность сессии (в секундах)
define('SESSION_LIFETIME', 86400); // 24 часа

// Максимальное количество попыток входа
define('MAX_LOGIN_ATTEMPTS', 5);

// Время блокировки после превышения попыток (в минутах)
define('LOCKOUT_TIME', 15);

// ==================== НАСТРОЙКИ EMAIL ====================
// Email администратора для уведомлений
define('ADMIN_EMAIL', 'admin@yourdomain.com');

// Отправитель по умолчанию
define('DEFAULT_FROM_EMAIL', 'noreply@yourdomain.com');
define('DEFAULT_FROM_NAME', 'Warranty SaaS');

// Тема письма для регистрации
define('REGISTRATION_EMAIL_SUBJECT', 'Новая заявка на регистрацию');

// ==================== НАСТРОЙКИ ФАЙЛОВ ====================
// Максимальный размер загружаемого файла (в байтах)
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB

// Разрешённые типы файлов для логотипа
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Разрешённые расширения
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ==================== НАСТРОЙКИ ПРИЛОЖЕНИЯ ====================
// Режим разработки (true - показывать ошибки)
define('DEBUG_MODE', false);

// Таймзона
date_default_timezone_set('Europe/Moscow');

// Локаль
setlocale(LC_TIME, 'ru_RU.UTF-8');

// ==================== НАСТРОЙКИ ПО УМОЛЧАНИЮ ====================
// Цвета по умолчанию для новых форм
define('DEFAULT_BUTTON_COLOR', '#c3202e');
define('DEFAULT_PRIMARY_COLOR', '#BF081A');
define('DEFAULT_BG_GRADIENT_START', '#f4f4f4');
define('DEFAULT_BG_GRADIENT_MIDDLE', '#3f3f3f');
define('DEFAULT_BG_GRADIENT_END', '#c3202e');

// ==================== ПОДКЛЮЧЕНИЕ К БД ====================
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    if (DEBUG_MODE) {
        die('Ошибка подключения к БД: ' . $e->getMessage());
    } else {
        die('Ошибка подключения к базе данных. Пожалуйста, повторите попытку позже.');
    }
}

// ==================== ОБРАБОТКА ОШИБОК ====================
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ==================== ФУНКЦИИ БЕЗОПАСНОСТИ ====================

/**
 * Экранирование HTML
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Генерация CSRF токена
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверка CSRF токена
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Безопасное перенаправление
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Проверка авторизации
 */
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        redirect(BASE_URL . '/login.php');
    }
}

/**
 * Проверка роли администратора
 */
function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        die('Доступ запрещён');
    }
}

/**
 * Получение текущего поддомена
 */
function getCurrentSubdomain() {
    $host = $_SERVER['HTTP_HOST'];
    $parts = explode('.', $host);
    
    // Если это не главный домен
    if (count($parts) > 2) {
        return $parts[0];
    }
    
    return null;
}

/**
 * Генерация случайного поддомена
 */
function generateRandomSubdomain($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $subdomain = '';
    for ($i = 0; $i < $length; $i++) {
        $subdomain .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $subdomain;
}

/**
 * Проверка доступности поддомена
 */
function isSubdomainAvailable($subdomain, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE subdomain = ?");
    $stmt->execute([$subdomain]);
    return $stmt->fetchColumn() == 0;
}

/**
 * Логирование действий
 */
function logActivity($action, $description = null, $user_id = null) {
    global $pdo;
    
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, description, ip_address) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user_id,
        $action,
        $description,
        $_SERVER['REMOTE_ADDR']
    ]);
}

/**
 * Отправка email
 */
function sendEmail($to, $subject, $message, $from = null) {
    if ($from === null) {
        $from = DEFAULT_FROM_EMAIL;
    }
    
    $headers = [
        'From: ' . $from,
        'Reply-To: ' . $from,
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

// ==================== ИНИЦИАЛИЗАЦИЯ СЕССИИ ====================
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'cookie_httponly' => true,
    'cookie_secure' => USE_HTTPS,
    'cookie_samesite' => 'Lax'
]);

// Регенерация ID сессии для безопасности
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = true;
}

// Проверка истечения сессии
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

?>
