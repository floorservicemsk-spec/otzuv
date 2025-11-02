-- База данных для SaaS сервиса управления формами гарантии
-- Версия: 3.0 - Фиксированная структура warranty.html с редактируемыми labels

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

-- Таблица labels для полей формы (только названия, структура фиксирована)
CREATE TABLE `form_labels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL COMMENT 'Номер шага формы',
  `step_title` varchar(500) DEFAULT NULL COMMENT 'Заголовок шага',
  `step_subtitle` text DEFAULT NULL COMMENT 'Подзаголовок шага',
  `field_label` varchar(500) DEFAULT NULL COMMENT 'Label для поля ввода',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_step` (`user_id`, `step_number`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Таблица карточек товаров (блок 6 - скидки)
CREATE TABLE `discount_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `card_order` int(11) NOT NULL DEFAULT 0,
  `card_title` varchar(255) NOT NULL,
  `card_text` varchar(255) NOT NULL,
  `card_image` varchar(500) DEFAULT NULL,
  `card_value` varchar(100) NOT NULL COMMENT 'Значение для отправки',
  `is_enabled` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `card_order` (`card_order`),
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
  `expires_at` timestamp NOT NULL,
  `data` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `last_activity` (`last_activity`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
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
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- ДЕФОЛТНЫЕ ДАННЫЕ
-- --------------------------------------------------------

-- Создание администратора
INSERT INTO `users` (`email`, `password`, `role`, `form_id`, `company_name`, `status`) VALUES
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin000', 'Администратор', 'approved');

-- Создание демо-клиента
INSERT INTO `users` (`email`, `password`, `role`, `form_id`, `company_name`, `status`) VALUES
('demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'demo1234', 'Демо компания', 'approved');

-- --------------------------------------------------------
-- ТРИГГЕРЫ ДЛЯ АВТОМАТИЧЕСКОГО СОЗДАНИЯ НАСТРОЕК
-- --------------------------------------------------------

DELIMITER $$

-- Триггер для создания дефолтных настроек дизайна
CREATE TRIGGER create_default_design_settings 
AFTER INSERT ON `users`
FOR EACH ROW
BEGIN
    INSERT INTO `form_design` (user_id, logo_url, button_color, primary_color, background_gradient_start, background_gradient_middle, background_gradient_end)
    VALUES (NEW.id, NULL, '#c3202e', '#BF081A', '#f4f4f4', '#3f3f3f', '#c3202e');
END$$

-- Триггер для создания дефолтных настроек интеграций
CREATE TRIGGER create_default_integration_settings 
AFTER INSERT ON `users`
FOR EACH ROW
BEGIN
    INSERT INTO `form_integrations` (user_id, email_enabled, email_to)
    VALUES (NEW.id, 1, NEW.email);
END$$

-- Триггер для создания дефолтных labels шагов
CREATE TRIGGER create_default_labels 
AFTER INSERT ON `users`
FOR EACH ROW
BEGIN
    -- Шаг 1: Идентификация
    INSERT INTO `form_labels` (user_id, step_number, step_title, step_subtitle) VALUES
    (NEW.id, 1, 'Идентификация', 'Пожалуйста, введите номер телефона или договора, на который был сделан заказ');
    
    -- Шаг 2: Дополнительные работы
    INSERT INTO `form_labels` (user_id, step_number, step_title, step_subtitle) VALUES
    (NEW.id, 2, 'Дополнительные работы, которые не вошли в договор', 'Если были дополнительные работы, которые не перечислены в договоре, укажите их здесь, чтобы включить их в гарантию. Вы оплачивали дополнительные работы, незафиксированные в договоре?');
    
    -- Шаг 3: Работа продавцов
    INSERT INTO `form_labels` (user_id, step_number, step_title, step_subtitle) VALUES
    (NEW.id, 3, 'Работа продавцов', 'Оцените по 5-балльной шкале, насколько продавец был внимателен к вашим желаниям и подбирал лучшее решение');
    
    -- Шаг 4: Работа доставки
    INSERT INTO `form_labels` (user_id, step_number, step_title, step_subtitle) VALUES
    (NEW.id, 4, 'Работа доставки', 'Оцените по 5-балльной шкале, насколько быстро и аккуратно доставили Вашу покупку');
    
    -- Шаг 5: Работа монтажников
    INSERT INTO `form_labels` (user_id, step_number, step_title, step_subtitle) VALUES
    (NEW.id, 5, 'Работа монтажников (если заказывали монтаж в нашей компании)', 'Оцените по 5-балльной шкале, насколько качественно уложили напольное покрытие');
    
    -- Шаг 6: Скидки
    INSERT INTO `form_labels` (user_id, step_number, step_title, step_subtitle) VALUES
    (NEW.id, 6, 'Забронируйте скидку на сопутствующие товары и укладку', 'Можете выбрать один или несколько вариантов');
END$$

-- Триггер для создания дефолтных карточек скидок
CREATE TRIGGER create_default_discount_cards 
AFTER INSERT ON `users`
FOR EACH ROW
BEGIN
    INSERT INTO `discount_cards` (user_id, card_order, card_title, card_text, card_image, card_value, is_enabled) VALUES
    (NEW.id, 1, 'Клей', 'Скидка 10%', '/images/glue.jpg', 'Клей', 1),
    (NEW.id, 2, 'Плинтус', 'Скидка 5%', '/images/baseboard.jpg', 'Плинтус', 1),
    (NEW.id, 3, 'Подложка', 'Скидка 5%', '/images/underlay.jpg', 'Подложка', 1),
    (NEW.id, 4, 'Грунтовка', 'Скидка 10%', '/images/primer.jpg', 'Грунтовка', 1),
    (NEW.id, 5, 'Укладка', 'Скидка 30%', '/images/installation.jpg', 'Укладка', 1);
END$$

DELIMITER ;

-- --------------------------------------------------------
-- INDEXES
-- --------------------------------------------------------

CREATE INDEX idx_form_labels_user_step ON form_labels(user_id, step_number);
CREATE INDEX idx_discount_cards_user_order ON discount_cards(user_id, card_order);
CREATE INDEX idx_submissions_user_date ON form_submissions(user_id, submitted_at DESC);

-- --------------------------------------------------------
-- КОММЕНТАРИИ
-- --------------------------------------------------------

-- form_labels: Хранит только НАЗВАНИЯ (labels) для каждого шага формы
-- Структура формы фиксированная (6 шагов из warranty.html)
-- Пользователь может менять только тексты названий

-- discount_cards: Карточки товаров/услуг для блока 6 (скидки)
-- Пользователь может менять названия, тексты, изображения и порядок

-- Удалена таблица form_fields из v2.0 (больше не нужна)
-- Теперь форма имеет фиксированную структуру как в warranty.html
