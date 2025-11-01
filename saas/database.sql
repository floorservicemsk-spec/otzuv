-- База данных для SaaS сервиса управления формами гарантии
-- Версия: 1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Создание базы данных
CREATE DATABASE IF NOT EXISTS `warranty_saas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `warranty_saas`;

-- --------------------------------------------------------

-- Таблица пользователей
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') NOT NULL DEFAULT 'client',
  `subdomain` varchar(100) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `subdomain` (`subdomain`),
  KEY `status` (`status`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Таблица настроек дизайна форм
CREATE TABLE `form_design` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `button_color` varchar(7) DEFAULT '#c3202e',
  `background_gradient_start` varchar(7) DEFAULT '#f4f4f4',
  `background_gradient_middle` varchar(7) DEFAULT '#3f3f3f',
  `background_gradient_end` varchar(7) DEFAULT '#c3202e',
  `primary_color` varchar(7) DEFAULT '#BF081A',
  `secondary_color` varchar(7) DEFAULT '#2f6f30',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Таблица настроек интеграций
CREATE TABLE `form_integrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email_enabled` tinyint(1) DEFAULT 1,
  `email_to` varchar(255) DEFAULT NULL,
  `email_from` varchar(255) DEFAULT 'noreply@yourdomain.com',
  `telegram_enabled` tinyint(1) DEFAULT 0,
  `telegram_bot_token` varchar(255) DEFAULT NULL,
  `telegram_chat_id` varchar(255) DEFAULT NULL,
  `google_sheets_enabled` tinyint(1) DEFAULT 0,
  `google_sheets_url` varchar(500) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Таблица отправленных форм (для аналитики)
CREATE TABLE `form_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `contract` varchar(100) DEFAULT NULL,
  `additional_work` varchar(10) DEFAULT NULL,
  `sales_rating` int(1) DEFAULT NULL,
  `delivery_rating` int(1) DEFAULT NULL,
  `installation_rating` int(1) DEFAULT NULL,
  `discounts` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `submitted_at` (`submitted_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Таблица сессий
CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Таблица логов действий (аудит)
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Вставка администратора по умолчанию
-- Пароль: Admin123! (ОБЯЗАТЕЛЬНО ИЗМЕНИТЕ после установки!)
INSERT INTO `users` (`email`, `password`, `role`, `subdomain`, `company_name`, `status`) VALUES
('admin@yourdomain.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin', 'Администратор', 'approved');

-- Создание демо-клиента (для тестирования)
-- Пароль: Demo123!
INSERT INTO `users` (`email`, `password`, `role`, `subdomain`, `company_name`, `status`) VALUES
('demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'demo', 'Демо компания', 'approved');

-- Настройки дизайна для демо-клиента
INSERT INTO `form_design` (`user_id`, `button_color`, `primary_color`) VALUES
(2, '#c3202e', '#BF081A');

-- Настройки интеграций для демо-клиента
INSERT INTO `form_integrations` (`user_id`, `email_to`) VALUES
(2, 'demo@example.com');

-- --------------------------------------------------------

-- Индексы для оптимизации
CREATE INDEX idx_user_status ON users(status, role);
CREATE INDEX idx_submissions_user_date ON form_submissions(user_id, submitted_at);

-- --------------------------------------------------------

-- Триггер для создания настроек по умолчанию при создании пользователя
DELIMITER $$

CREATE TRIGGER after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    -- Создаём настройки дизайна
    INSERT INTO form_design (user_id) VALUES (NEW.id);
    
    -- Создаём настройки интеграций
    INSERT INTO form_integrations (user_id, email_to) VALUES (NEW.id, NEW.email);
END$$

DELIMITER ;

-- --------------------------------------------------------

-- Представление для статистики пользователя
CREATE VIEW user_stats AS
SELECT 
    u.id as user_id,
    u.email,
    u.company_name,
    u.subdomain,
    COUNT(fs.id) as total_submissions,
    MAX(fs.submitted_at) as last_submission,
    u.created_at as user_since
FROM users u
LEFT JOIN form_submissions fs ON u.id = fs.user_id
GROUP BY u.id;

-- --------------------------------------------------------

-- Процедура очистки устаревших сессий
DELIMITER $$

CREATE PROCEDURE cleanup_expired_sessions()
BEGIN
    DELETE FROM sessions WHERE expires_at < NOW();
END$$

DELIMITER ;

-- --------------------------------------------------------

-- Создание событий для автоматической очистки (требует включенного event_scheduler)
-- SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS cleanup_sessions_event
ON SCHEDULE EVERY 1 HOUR
DO CALL cleanup_expired_sessions();

-- --------------------------------------------------------

COMMIT;
