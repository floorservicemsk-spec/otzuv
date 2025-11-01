# 📦 Установка Warranty SaaS

Полная инструкция по установке и настройке системы.

---

## 📋 Требования

- **PHP**: 7.4 или выше
- **MySQL**: 5.7 или выше
- **Apache/Nginx**: с mod_rewrite (Apache) или аналогом (Nginx)
- **SSL сертификат**: для безопасной работы (рекомендуется Let's Encrypt)
- **Поддержка поддоменов**: wildcard DNS или настройка отдельных записей

---

## 🚀 Шаг 1: Настройка базы данных

1. Создайте новую базу данных:
```sql
CREATE DATABASE warranty_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Создайте пользователя базы данных:
```sql
CREATE USER 'warranty_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON warranty_saas.* TO 'warranty_user'@'localhost';
FLUSH PRIVILEGES;
```

3. Импортируйте схему:
```bash
mysql -u warranty_user -p warranty_saas < database.sql
```

---

## ⚙️ Шаг 2: Настройка конфигурации

Откройте файл `config.php` и настройте параметры:

```php
// База данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'warranty_saas');
define('DB_USER', 'warranty_user');
define('DB_PASS', 'your_strong_password');

// Домен
define('MAIN_DOMAIN', 'yourservice.com'); // Ваш основной домен

// Email
define('ADMIN_EMAIL', 'admin@yourservice.com');
define('DEFAULT_FROM_EMAIL', 'noreply@yourservice.com');

// Секретный ключ (сгенерируйте случайную строку)
define('SECRET_KEY', 'ваш_случайный_секретный_ключ_здесь');
```

### Генерация SECRET_KEY:
```php
echo bin2hex(random_bytes(32));
```

---

## 🌐 Шаг 3: Настройка Apache

### Для поддержки поддоменов создайте VirtualHost:

```apache
<VirtualHost *:80>
    ServerName yourservice.com
    ServerAlias *.yourservice.com
    
    DocumentRoot /path/to/warranty-saas/saas
    
    <Directory /path/to/warranty-saas/saas>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/warranty_error.log
    CustomLog ${APACHE_LOG_DIR}/warranty_access.log combined
</VirtualHost>
```

### Включите необходимые модули:
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

---

## 🔒 Шаг 4: SSL сертификат (Let's Encrypt)

```bash
sudo apt-get install certbot python3-certbot-apache
sudo certbot --apache -d yourservice.com -d *.yourservice.com
```

После установки сертификата, Apache автоматически создаст VirtualHost на порту 443.

---

## 📁 Шаг 5: Права доступа к файлам

```bash
# Установка владельца
sudo chown -R www-data:www-data /path/to/warranty-saas

# Установка прав
sudo chmod -R 755 /path/to/warranty-saas
sudo chmod -R 775 /path/to/warranty-saas/saas/uploads
```

---

## 🔧 Шаг 6: .htaccess для Apache

Создайте файл `.htaccess` в корне `/saas`:

```apache
RewriteEngine On

# Перенаправление HTTP на HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Маршрутизация для поддоменов
RewriteCond %{HTTP_HOST} !^www\. [NC]
RewriteCond %{HTTP_HOST} !^yourservice\.com$ [NC]
RewriteCond %{REQUEST_URI} !^/form\.php$
RewriteCond %{REQUEST_URI} !^/widget\.js$
RewriteCond %{REQUEST_URI} !^/api/
RewriteCond %{REQUEST_URI} !^/assets/
RewriteRule ^(.*)$ /form.php [L]

# Защита конфигурации
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

# Защита от просмотра директорий
Options -Indexes
```

---

## 🌐 Шаг 7: Настройка DNS

### Для wildcard поддоменов:
```
A     yourservice.com        -> ваш_IP
A     *.yourservice.com      -> ваш_IP
```

### Или для отдельных поддоменов:
```
A     subdomain1.yourservice.com    -> ваш_IP
A     subdomain2.yourservice.com    -> ваш_IP
```

---

## 👤 Шаг 8: Создание администратора

По умолчанию создается администратор:
- **Email**: `sundoze87@gmail.com`
- **Пароль**: `nifrit2303!@#`

⚠️ **ВАЖНО**: После импорта database.sql выполните настройку:

```bash
php setup_admin.php
```

Или вручную:

```bash
HASH=$(php -r "echo password_hash('nifrit2303!@#', PASSWORD_BCRYPT);")
mysql -u warranty_user -p warranty_saas -e "UPDATE users SET email='sundoze87@gmail.com', password='$HASH' WHERE role='admin';"
```

Подробнее: [ADMIN_SETUP.md](ADMIN_SETUP.md)

Или войдите через `/login.php` и измените пароль в интерфейсе.

---

## 🧪 Шаг 9: Тестирование

1. Откройте `https://yourservice.com/login.php`
2. Войдите как администратор
3. Создайте тестового пользователя через `/register.php`
4. Подтвердите пользователя в админ-панели
5. Откройте форму на поддомене: `https://testuser.yourservice.com`

---

## 📧 Шаг 10: Настройка Email (опционально)

Для корректной отправки email настройте SMTP или используйте сервис вроде SendGrid/Mailgun.

Пример настройки SMTP в `config.php`:

```php
function sendEmail($to, $subject, $message, $from = null) {
    require_once 'vendor/PHPMailer/PHPMailer.php';
    require_once 'vendor/PHPMailer/SMTP.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your@gmail.com';
        $mail->Password = 'your_app_password';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom($from ?: DEFAULT_FROM_EMAIL);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
```

---

## 🛠️ Устранение неполадок

### Проблема: "Пользователь не найден"
- Проверьте настройку DNS
- Убедитесь, что wildcard работает: `nslookup test.yourservice.com`

### Проблема: Ошибки базы данных
- Проверьте учетные данные в `config.php`
- Убедитесь, что схема импортирована

### Проблема: Email не отправляются
- Проверьте логи: `/var/log/apache2/error.log`
- Настройте SMTP или используйте внешний сервис

### Проблема: 500 Internal Server Error
- Проверьте права доступа к файлам
- Включите отображение ошибок: `ini_set('display_errors', 1);`

---

## 📚 Дополнительно

- **Telegram**: [TELEGRAM_SETUP.md](TELEGRAM_SETUP.md)
- **Google Sheets**: [GOOGLE_SHEETS_SETUP.md](GOOGLE_SHEETS_SETUP.md)
- **Виджет**: [WIDGET_SETUP.md](WIDGET_SETUP.md)

---

## 🔐 Безопасность

1. Измените `SECRET_KEY` в `config.php`
2. Используйте сложные пароли для БД
3. Установите SSL сертификат
4. Регулярно обновляйте систему
5. Ограничьте доступ к `config.php`
6. Включите `DEBUG_MODE = false` в продакшене

---

## 📞 Поддержка

Если возникли проблемы, проверьте:
- Логи Apache: `/var/log/apache2/`
- Логи PHP: `php.ini` -> `error_log`
- Права доступа к файлам
- Настройки DNS

---

Готово! Ваш Warranty SaaS установлен и готов к работе! 🎉
