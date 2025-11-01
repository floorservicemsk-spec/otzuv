<?php
/**
 * Выход из системы
 */
define('SAAS_SYSTEM', true);
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    logActivity('logout', 'Выход из системы');
}

// Уничтожение сессии
session_unset();
session_destroy();

// Перенаправление на страницу входа
redirect(BASE_URL . '/login.php');
?>
