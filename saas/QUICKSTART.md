# 🚀 Быстрый старт Warranty SaaS

Пошаговая инструкция для запуска сервиса на вашем сервере.

---

## 📋 Что вам понадобится

- Сервер с Ubuntu 20.04+ (или любой Linux)
- Root доступ к серверу
- Домен (например, `yourservice.com`)
- 30-60 минут времени

---

## 🎯 Выберите ваш сценарий

### Сценарий A: Локальный сервер (для тестирования)
Быстрый запуск на вашем компьютере для тестирования.
👉 [Перейти к инструкции](#сценарий-a-локальный-сервер)

### Сценарий B: VPS сервер (production)
Полноценная установка на VPS (DigitalOcean, AWS, etc).
👉 [Перейти к инструкции](#сценарий-b-vps-сервер-production)

### Сценарий C: Shared хостинг
Установка на обычном хостинге (если поддерживаются поддомены).
👉 [Перейти к инструкции](#сценарий-c-shared-хостинг)

---

# Сценарий A: Локальный сервер

Для тестирования на вашем компьютере (Windows/Mac/Linux).

## Шаг 1: Установка XAMPP (Windows/Mac)

### Windows:
1. Скачайте XAMPP: https://www.apachefriends.org/download.html
2. Установите (выберите Apache, MySQL, PHP)
3. Запустите XAMPP Control Panel
4. Запустите Apache и MySQL

### Mac:
```bash
brew install --cask xampp
```

### Linux:
```bash
sudo apt update
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-curl
```

## Шаг 2: Создание базы данных

1. Откройте браузер: `http://localhost/phpmyadmin`
2. Войдите (по умолчанию: root, без пароля)
3. Нажмите "Создать базу данных"
4. Имя: `warranty_saas`
5. Кодировка: `utf8mb4_unicode_ci`
6. Нажмите "Создать"

## Шаг 3: Импорт схемы БД

1. Выберите базу `warranty_saas`
2. Вкладка "Импорт"
3. Нажмите "Выберите файл"
4. Найдите `/workspace/saas/database.sql`
5. Нажмите "Вперёд"
6. Дождитесь сообщения "Импорт успешно завершён"

## Шаг 4: Копирование файлов

### Windows (XAMPP):
```cmd
xcopy /E /I C:\путь\к\workspace\saas C:\xampp\htdocs\warranty-saas
```

### Mac (XAMPP):
```bash
cp -r /путь/к/workspace/saas /Applications/XAMPP/htdocs/warranty-saas
```

### Linux:
```bash
sudo cp -r /workspace/saas /var/www/html/warranty-saas
sudo chown -R www-data:www-data /var/www/html/warranty-saas
```

## Шаг 5: Настройка config.php

Откройте файл `config.php` в текстовом редакторе:

**Windows/Mac (XAMPP):**
```
C:\xampp\htdocs\warranty-saas\config.php
```

**Linux:**
```
/var/www/html/warranty-saas/config.php
```

Измените следующие строки:

```php
// База данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'warranty_saas');
define('DB_USER', 'root');
define('DB_PASS', '');  // Оставьте пустым для XAMPP

// Домен (для локального тестирования используем localhost)
define('MAIN_DOMAIN', 'localhost');

// Протокол (для локального HTTP, без SSL)
define('PROTOCOL', 'http://');

// Секретный ключ (ОБЯЗАТЕЛЬНО смените!)
define('SECRET_KEY', 'ваш_случайный_ключ_минимум_32_символа');
```

### Генерация SECRET_KEY:

Создайте файл `generate_key.php` рядом с config.php:
```php
<?php
echo bin2hex(random_bytes(32));
?>
```

Откройте в браузере: `http://localhost/warranty-saas/generate_key.php`
Скопируйте ключ и вставьте в `SECRET_KEY`.
Удалите файл `generate_key.php`.

## Шаг 6: Настройка виртуальных хостов (для поддоменов)

### Windows (XAMPP):

1. Откройте файл hosts:
```
C:\Windows\System32\drivers\etc\hosts
```

2. Добавьте строки:
```
127.0.0.1    warranty.local
127.0.0.1    test.warranty.local
127.0.0.1    demo.warranty.local
```

3. Откройте файл httpd-vhosts.conf:
```
C:\xampp\apache\conf\extra\httpd-vhosts.conf
```

4. Добавьте:
```apache
<VirtualHost *:80>
    ServerName warranty.local
    ServerAlias *.warranty.local
    DocumentRoot "C:/xampp/htdocs/warranty-saas"
    
    <Directory "C:/xampp/htdocs/warranty-saas">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

5. Перезапустите Apache в XAMPP Control Panel

### Mac/Linux:

1. Откройте файл hosts:
```bash
sudo nano /etc/hosts
```

2. Добавьте:
```
127.0.0.1    warranty.local
127.0.0.1    test.warranty.local
```

3. Создайте конфигурацию Apache:
```bash
sudo nano /etc/apache2/sites-available/warranty.conf
```

4. Добавьте:
```apache
<VirtualHost *:80>
    ServerName warranty.local
    ServerAlias *.warranty.local
    DocumentRoot /var/www/html/warranty-saas
    
    <Directory /var/www/html/warranty-saas>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

5. Включите сайт:
```bash
sudo a2ensite warranty.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

6. Обновите MAIN_DOMAIN в config.php:
```php
define('MAIN_DOMAIN', 'warranty.local');
```

## Шаг 7: Первый вход

1. Откройте браузер
2. Перейдите: `http://warranty.local/login.php`
3. Войдите:
   - Email: `admin@yourservice.com`
   - Password: `admin123`
4. **СРАЗУ СМЕНИТЕ ПАРОЛЬ!**

## Шаг 8: Создание тестового пользователя

1. Откройте: `http://warranty.local/register.php`
2. Заполните форму:
   - Email: `test@test.com`
   - Компания: `Test Company`
   - Поддомен: `test`
   - Пароль: `test1234`
3. Нажмите "Зарегистрироваться"
4. Вернитесь в админ-панель: `http://warranty.local/admin/users.php`
5. Одобрите пользователя

## Шаг 9: Проверка формы

Откройте: `http://test.warranty.local/form.php`

Вы должны увидеть форму активации гарантии!

✅ **Готово! Локальный сервер работает!**

---

# Сценарий B: VPS сервер (Production)

Полноценная установка на сервере для production.

## Предварительные требования

- VPS сервер (Ubuntu 20.04+)
- Root доступ по SSH
- Домен с доступом к DNS

## Шаг 1: Подключение к серверу

```bash
ssh root@your-server-ip
```

## Шаг 2: Обновление системы

```bash
apt update && apt upgrade -y
```

## Шаг 3: Установка LAMP

```bash
# Apache
apt install apache2 -y

# MySQL
apt install mysql-server -y

# PHP
apt install php libapache2-mod-php php-mysql php-curl php-mbstring php-xml -y

# Проверка версий
apache2 -v
mysql --version
php -v
```

## Шаг 4: Настройка MySQL

```bash
# Запуск скрипта безопасности
mysql_secure_installation
```

Отвечайте на вопросы:
- VALIDATE PASSWORD COMPONENT: `Y` (да)
- Password strength: `2` (сильный)
- Новый root пароль: `ваш_надежный_пароль`
- Remove anonymous users: `Y`
- Disallow root login remotely: `Y`
- Remove test database: `Y`
- Reload privilege tables: `Y`

## Шаг 5: Создание базы данных

```bash
mysql -u root -p
```

Введите пароль, затем выполните:

```sql
CREATE DATABASE warranty_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'warranty_user'@'localhost' IDENTIFIED BY 'сложный_пароль_123';

GRANT ALL PRIVILEGES ON warranty_saas.* TO 'warranty_user'@'localhost';

FLUSH PRIVILEGES;

EXIT;
```

## Шаг 6: Загрузка файлов на сервер

### Вариант A: Через Git (рекомендуется)

```bash
cd /var/www/html
git clone https://github.com/yourusername/warranty-saas.git
cd warranty-saas/saas
```

### Вариант B: Через SCP

На вашем компьютере:
```bash
scp -r /workspace/saas root@your-server-ip:/var/www/html/warranty-saas
```

## Шаг 7: Импорт схемы БД

```bash
mysql -u warranty_user -p warranty_saas < /var/www/html/warranty-saas/database.sql
```

Введите пароль пользователя `warranty_user`.

## Шаг 8: Настройка config.php

```bash
nano /var/www/html/warranty-saas/config.php
```

Измените:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'warranty_saas');
define('DB_USER', 'warranty_user');
define('DB_PASS', 'сложный_пароль_123');

define('MAIN_DOMAIN', 'yourservice.com');  // ВАШ ДОМЕН!
define('PROTOCOL', 'https://');  // HTTPS для production

define('SECRET_KEY', 'сгенерируйте_здесь_случайный_ключ');

define('ADMIN_EMAIL', 'admin@yourservice.com');
define('DEFAULT_FROM_EMAIL', 'noreply@yourservice.com');
```

Сгенерировать SECRET_KEY:
```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

Сохраните: `Ctrl+O`, `Enter`, `Ctrl+X`

## Шаг 9: Установка прав доступа

```bash
chown -R www-data:www-data /var/www/html/warranty-saas
chmod -R 755 /var/www/html/warranty-saas
chmod -R 775 /var/www/html/warranty-saas/uploads
```

## Шаг 10: Настройка Apache

### A. Создание VirtualHost

```bash
nano /etc/apache2/sites-available/warranty.conf
```

Вставьте:

```apache
<VirtualHost *:80>
    ServerName yourservice.com
    ServerAlias *.yourservice.com
    
    DocumentRoot /var/www/html/warranty-saas
    
    <Directory /var/www/html/warranty-saas>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/warranty_error.log
    CustomLog ${APACHE_LOG_DIR}/warranty_access.log combined
</VirtualHost>
```

### B. Включение сайта

```bash
a2ensite warranty.conf
a2enmod rewrite
a2dissite 000-default.conf  # Отключить дефолтный сайт
systemctl restart apache2
```

## Шаг 11: Настройка DNS

Перейдите в панель управления вашим доменом (например, Cloudflare, Namecheap) и добавьте A-записи:

```
Тип    Имя                     Значение
A      @                       ваш_server_ip
A      *                       ваш_server_ip
```

Примеры:
```
A      yourservice.com         123.45.67.89
A      *.yourservice.com       123.45.67.89
```

⚠️ DNS может обновляться до 24 часов, но обычно 5-15 минут.

## Шаг 12: Установка SSL (Let's Encrypt)

```bash
# Установка Certbot
apt install certbot python3-certbot-apache -y

# Получение сертификата
certbot --apache -d yourservice.com -d *.yourservice.com
```

⚠️ **Важно**: для wildcard сертификата (*.yourservice.com) нужна DNS-валидация. Certbot покажет инструкции.

Отвечайте на вопросы:
- Email: `ваш@email.com`
- Terms of Service: `A` (agree)
- Share email: `N` (no)
- Redirect HTTP to HTTPS: `2` (yes)

### Альтернатива (без wildcard):

```bash
certbot --apache -d yourservice.com -d test.yourservice.com -d demo.yourservice.com
```

Добавляйте каждый поддомен отдельно.

## Шаг 13: Автообновление SSL

```bash
# Проверка автообновления
systemctl status certbot.timer

# Тест обновления
certbot renew --dry-run
```

## Шаг 14: Настройка Firewall

```bash
# UFW
ufw allow 'Apache Full'
ufw allow OpenSSH
ufw enable

# Проверка
ufw status
```

## Шаг 15: Первый вход

1. Откройте: `https://yourservice.com/login.php`
2. Войдите:
   - Email: `admin@yourservice.com`
   - Password: `admin123`
3. **СРАЗУ СМЕНИТЕ ПАРОЛЬ!**

## Шаг 16: Проверка работы

1. Зарегистрируйте тестового пользователя: `/register.php`
2. Одобрите в админке: `/admin/users.php`
3. Откройте форму: `https://testuser.yourservice.com`

✅ **Production сервер готов!**

---

# Сценарий C: Shared хостинг

Для обычных хостингов (cPanel, Plesk, etc).

## Требования к хостингу

- ✅ PHP 7.4+
- ✅ MySQL 5.7+
- ✅ Поддержка поддоменов (wildcard или ручное добавление)
- ✅ mod_rewrite (обычно включен)
- ✅ SSH доступ (желательно)

## Шаг 1: Загрузка файлов

### Через FTP:
1. Подключитесь к FTP (FileZilla, WinSCP)
2. Загрузите папку `/workspace/saas` в `public_html/warranty-saas`

### Через cPanel File Manager:
1. Войдите в cPanel
2. Откройте File Manager
3. Загрузите ZIP с файлами
4. Распакуйте в `public_html/warranty-saas`

## Шаг 2: Создание базы данных (cPanel)

1. cPanel → MySQL Databases
2. Create New Database: `warranty_saas`
3. Create New User:
   - Username: `warranty_user`
   - Password: `сложный_пароль`
4. Add User to Database
5. Выберите ALL PRIVILEGES
6. Запомните:
   - Database name: обычно `username_warranty_saas`
   - User: `username_warranty_user`

## Шаг 3: Импорт схемы

1. cPanel → phpMyAdmin
2. Выберите базу `username_warranty_saas`
3. Вкладка "Импорт"
4. Загрузите `database.sql`
5. Нажмите "Вперёд"

## Шаг 4: Настройка config.php

Через File Manager или FTP откройте `config.php`:

```php
define('DB_HOST', 'localhost');  // Иногда это IP, уточните у хостера
define('DB_NAME', 'username_warranty_saas');
define('DB_USER', 'username_warranty_user');
define('DB_PASS', 'ваш_пароль');

define('MAIN_DOMAIN', 'yourservice.com');
define('PROTOCOL', 'https://');

define('SECRET_KEY', 'сгенерируйте_ключ');
```

## Шаг 5: Настройка поддоменов

### cPanel:
1. Domains → Subdomains
2. Создайте:
   - Subdomain: `*` (wildcard, если поддерживается)
   - Document Root: `public_html/warranty-saas`

Если wildcard не поддерживается, создавайте каждый вручную:
- `test.yourservice.com` → `/public_html/warranty-saas`
- `demo.yourservice.com` → `/public_html/warranty-saas`

## Шаг 6: Настройка .htaccess

Убедитесь, что файл `.htaccess` загружен. Если нет, создайте в корне `warranty-saas`:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

RewriteCond %{HTTP_HOST} !^www\. [NC]
RewriteCond %{HTTP_HOST} !^yourservice\.com$ [NC]
RewriteCond %{REQUEST_URI} !^/form\.php$
RewriteCond %{REQUEST_URI} !^/widget\.js$
RewriteCond %{REQUEST_URI} !^/api/
RewriteCond %{REQUEST_URI} !^/assets/
RewriteCond %{REQUEST_URI} !^/uploads/
RewriteRule ^(.*)$ /form.php [L]

Options -Indexes
```

## Шаг 7: SSL сертификат

В cPanel:
1. SSL/TLS → Install SSL
2. Выберите домен
3. AutoSSL (если доступен)

Или используйте Let's Encrypt через cPanel.

## Шаг 8: Права доступа

Установите через File Manager:
- Папки: `755`
- Файлы: `644`
- Папка `uploads`: `775`

## Шаг 9: Первый вход

`https://yourservice.com/warranty-saas/login.php`

Email: `admin@yourservice.com`
Password: `admin123`

✅ **Shared хостинг готов!**

---

# 🔧 Устранение проблем

## Проблема: "Database connection failed"

**Решение:**
1. Проверьте данные в `config.php`
2. Убедитесь, что БД создана
3. Проверьте права пользователя MySQL
4. Проверьте `DB_HOST` (иногда это не `localhost`, а IP)

```bash
# Проверка подключения
mysql -u warranty_user -p warranty_saas
```

## Проблема: "Page not found" (404)

**Решение:**
1. Проверьте `.htaccess`
2. Убедитесь, что mod_rewrite включен:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```
3. Проверьте `AllowOverride All` в VirtualHost

## Проблема: Поддомены не работают

**Решение:**
1. Проверьте DNS (используйте nslookup):
```bash
nslookup test.yourservice.com
```
2. Проверьте VirtualHost: `ServerAlias *.yourservice.com`
3. Дождитесь распространения DNS (до 24ч)
4. Очистите кеш DNS:
```bash
# Windows
ipconfig /flushdns

# Mac
sudo dscacheutil -flushcache

# Linux
sudo systemd-resolve --flush-caches
```

## Проблема: "Permission denied"

**Решение:**
```bash
sudo chown -R www-data:www-data /var/www/html/warranty-saas
sudo chmod -R 755 /var/www/html/warranty-saas
sudo chmod -R 775 /var/www/html/warranty-saas/uploads
```

## Проблема: Email не отправляются

**Решение:**
1. Проверьте логи: `/var/log/apache2/error.log`
2. Настройте SMTP (см. INSTALLATION.md)
3. Используйте внешний сервис (SendGrid, Mailgun)

## Проблема: "Internal Server Error" (500)

**Решение:**
1. Проверьте логи:
```bash
tail -f /var/log/apache2/error.log
```
2. Включите отображение ошибок в `config.php`:
```php
define('DEBUG_MODE', true);
ini_set('display_errors', 1);
```
3. Проверьте синтаксис PHP:
```bash
php -l config.php
```

---

# ✅ Финальная проверка

После установки проверьте:

- [ ] `https://yourservice.com/login.php` - открывается
- [ ] Вход под admin работает
- [ ] `https://yourservice.com/register.php` - регистрация работает
- [ ] `https://yourservice.com/admin/users.php` - админ-панель доступна
- [ ] Создан тестовый пользователь
- [ ] Тестовый пользователь одобрен
- [ ] `https://testuser.yourservice.com` - форма открывается
- [ ] Форма отправляется
- [ ] Заявка появилась в `/dashboard/submissions.php`
- [ ] Email уведомление пришло (если настроено)
- [ ] SSL сертификат установлен (зеленый замок)
- [ ] Виджет работает на тестовой странице

---

# 📞 Нужна помощь?

1. **Проверьте логи:**
   - Apache: `/var/log/apache2/error.log`
   - MySQL: `/var/log/mysql/error.log`

2. **Включите debug режим** в `config.php`:
```php
define('DEBUG_MODE', true);
```

3. **Проверьте требования:**
```bash
php -v  # Должно быть 7.4+
mysql --version  # 5.7+
apache2 -v
```

4. **Тестируйте по частям:**
   - Сначала БД
   - Потом файлы
   - Потом Apache
   - Потом DNS
   - Потом SSL

---

# 🎉 Готово!

Теперь у вас работает полноценный SaaS-сервис!

**Что дальше?**
- Смените пароль администратора
- Настройте Email/Telegram/Google Sheets
- Пригласите первых клиентов
- Настройте мониторинг
- Настройте бэкапы БД

**Документация:**
- [README.md](README.md) - обзор возможностей
- [INSTALLATION.md](INSTALLATION.md) - детальная установка
- [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - полная сводка проекта

---

**Успехов! 🚀**
