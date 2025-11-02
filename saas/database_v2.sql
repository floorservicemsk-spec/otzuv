-- База данных для SaaS сервиса управления формами гарантии
-- Версия: 2.0 - Рефакторинг: формы вместо поддоменов

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
  `form_id` varchar(32) NOT NULL COMMENT 'Уникальный ID формы (abc123xyz)',
  `company_name` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `form_id` (`form_id`),
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

-- Таблица полей формы (кастомизация)
CREATE TABLE `form_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `field_key` varchar(50) NOT NULL COMMENT 'Ключ поля (phone, name, email, etc)',
  `field_label` varchar(255) NOT NULL COMMENT 'Название поля для пользователя',
  `field_type` enum('text','tel','email','number','textarea','rating','checkbox') NOT NULL DEFAULT 'text',
  `is_required` tinyint(1) DEFAULT 1,
  `is_enabled` tinyint(1) DEFAULT 1,
  `field_order` int(11) DEFAULT 0 COMMENT 'Порядок отображения',
  `placeholder` varchar(255) DEFAULT NULL,
  `validation_rules` text DEFAULT NULL COMMENT 'JSON с правилами валидации',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_field` (`user_id`, `field_key`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `field_order` (`field_order`)
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

-- Таблица заявок с формы
CREATE TABLE `form_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `form_data` longtext NOT NULL COMMENT 'JSON со всеми данными формы',
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `submitted_at` (`submitted_at`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Таблица сессий
CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `last_activity` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `last_activity` (`last_activity`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Таблица логов активности
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Индексы для производительности
CREATE INDEX idx_user_status ON users(status, role);
CREATE INDEX idx_submissions_user_date ON form_submissions(user_id, submitted_at);
CREATE INDEX idx_form_fields_order ON form_fields(user_id, field_order, is_enabled);

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
    
    -- Создаём дефолтные поля формы
    INSERT INTO form_fields (user_id, field_key, field_label, field_type, field_order, placeholder) VALUES
    (NEW.id, 'phone', 'Ваш номер телефона', 'tel', 1, '+7 (___) ___-__-__'),
    (NEW.id, 'name', 'Ваше имя', 'text', 2, 'Иван'),
    (NEW.id, 'email', 'Ваша электронная почта', 'email', 3, 'example@mail.com'),
    (NEW.id, 'sales_rating', 'Оцените работу менеджера продаж', 'rating', 4, NULL),
    (NEW.id, 'delivery_rating', 'Оцените работу доставки', 'rating', 5, NULL),
    (NEW.id, 'installation_rating', 'Оцените работу монтажников', 'rating', 6, NULL);
END$$

DELIMITER ;

-- --------------------------------------------------------

-- Представление для статистики пользователя
CREATE VIEW user_stats AS
SELECT 
    u.id as user_id,
    u.email,
    u.company_name,
    u.form_id,
    u.status,
    COUNT(DISTINCT fs.id) as total_submissions,
    MAX(fs.submitted_at) as last_submission
FROM users u
LEFT JOIN form_submissions fs ON u.id = fs.user_id
WHERE u.role = 'client'
GROUP BY u.id;

-- --------------------------------------------------------

-- Функция генерации уникального form_id
DELIMITER $$

CREATE FUNCTION generate_form_id() RETURNS VARCHAR(32)
DETERMINISTIC
BEGIN
    DECLARE new_id VARCHAR(32);
    DECLARE id_exists INT;
    
    -- Генерируем ID пока не найдём уникальный
    REPEAT
        SET new_id = LOWER(CONCAT(
            SUBSTRING(MD5(RAND()), 1, 8),
            SUBSTRING(MD5(RAND()), 1, 6)
        ));
        SELECT COUNT(*) INTO id_exists FROM users WHERE form_id = new_id;
    UNTIL id_exists = 0 END REPEAT;
    
    RETURN new_id;
END$$

DELIMITER ;

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

-- Вставка администратора по умолчанию
-- Email: sundoze87@gmail.com
-- Пароль: nifrit2303!@#
INSERT INTO `users` (`email`, `password`, `role`, `form_id`, `company_name`, `status`) VALUES
('sundoze87@gmail.com', '$2y$10$nifrit2303passwordhash.willbegenerated.afterinstallation', 'admin', 'admin000', 'Администратор', 'approved');

-- Создание демо-клиента (для тестирования)
INSERT INTO `users` (`email`, `password`, `role`, `form_id`, `company_name`, `status`) VALUES
('demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'demo12345678', 'Демо компания', 'approved');

-- Настройки дизайна для демо-клиента (создаются автоматически через триггер)

-- --------------------------------------------------------

-- Готово!
