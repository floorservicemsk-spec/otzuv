# ⚡ Шпаргалка - Warranty SaaS

Быстрые команды для запуска. Для подробностей см. [QUICKSTART.md](QUICKSTART.md)

---

## 🚀 Минимальная установка (5 минут)

### Ubuntu/Debian VPS:

```bash
# 1. Установка LAMP
apt update && apt upgrade -y
apt install apache2 mysql-server php libapache2-mod-php php-mysql php-curl -y

# 2. Создание БД
mysql -u root -p <<EOF
CREATE DATABASE warranty_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'warranty_user'@'localhost' IDENTIFIED BY 'ВАШ_ПАРОЛЬ';
GRANT ALL PRIVILEGES ON warranty_saas.* TO 'warranty_user'@'localhost';
FLUSH PRIVILEGES;
EOF

# 3. Загрузка файлов
cd /var/www/html
git clone YOUR_REPO_URL warranty-saas
cd warranty-saas

# 4. Импорт схемы
mysql -u warranty_user -p warranty_saas < database.sql

# 5. Настройка config.php
nano config.php
# Измените: DB_USER, DB_PASS, MAIN_DOMAIN, SECRET_KEY

# 6. Права доступа
chown -R www-data:www-data /var/www/html/warranty-saas
chmod -R 755 /var/www/html/warranty-saas
chmod -R 775 /var/www/html/warranty-saas/uploads

# 7. Apache VirtualHost
cat > /etc/apache2/sites-available/warranty.conf <<EOF
<VirtualHost *:80>
    ServerName yourservice.com
    ServerAlias *.yourservice.com
    DocumentRoot /var/www/html/warranty-saas
    <Directory /var/www/html/warranty-saas>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF

a2ensite warranty.conf
a2enmod rewrite
a2dissite 000-default.conf
systemctl restart apache2

# 8. SSL (Let's Encrypt)
apt install certbot python3-certbot-apache -y
certbot --apache -d yourservice.com

# 9. Firewall
ufw allow 'Apache Full'
ufw allow OpenSSH
ufw enable
```

**Готово!** Откройте `https://yourservice.com/login.php`

---

## 🔑 Дефолтный логин

```
Email: sundoze87@gmail.com
Password: nifrit2303!@#
```

⚠️ **Сразу смените пароль!**

---

## 📝 Генерация SECRET_KEY

```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

---

## 🔧 Проверка системы

```bash
# PHP версия (нужно 7.4+)
php -v

# MySQL версия (нужно 5.7+)
mysql --version

# Apache версия
apache2 -v

# Проверка БД
mysql -u warranty_user -p warranty_saas -e "SHOW TABLES;"

# Проверка прав
ls -la /var/www/html/warranty-saas

# Проверка логов
tail -f /var/log/apache2/error.log
```

---

## 🌐 DNS настройка

### Cloudflare / Namecheap:

```
Type    Name                    Content
A       @                       ваш_IP
A       *                       ваш_IP
```

Проверка:
```bash
nslookup yourservice.com
nslookup test.yourservice.com
```

---

## 🔄 Обновление системы

```bash
cd /var/www/html/warranty-saas
git pull origin main
mysql -u warranty_user -p warranty_saas < database.sql  # если были изменения
systemctl restart apache2
```

---

## 🐛 Быстрое устранение проблем

### "Database connection failed"
```bash
# Проверка
mysql -u warranty_user -p warranty_saas

# Если ошибка - создайте заново:
mysql -u root -p -e "DROP USER 'warranty_user'@'localhost';"
mysql -u root -p -e "CREATE USER 'warranty_user'@'localhost' IDENTIFIED BY 'новый_пароль';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON warranty_saas.* TO 'warranty_user'@'localhost';"
```

### "Page not found" (404)
```bash
# Проверка mod_rewrite
apache2ctl -M | grep rewrite

# Если нет - включить:
a2enmod rewrite
systemctl restart apache2
```

### "Permission denied"
```bash
chown -R www-data:www-data /var/www/html/warranty-saas
chmod -R 755 /var/www/html/warranty-saas
chmod -R 775 /var/www/html/warranty-saas/uploads
```

### Поддомены не работают
```bash
# Проверка DNS
nslookup test.yourservice.com

# Очистка кеша DNS (локально)
# Linux:
sudo systemd-resolve --flush-caches

# Проверка VirtualHost
cat /etc/apache2/sites-enabled/warranty.conf | grep ServerAlias
# Должно быть: ServerAlias *.yourservice.com
```

---

## 📊 Полезные команды

```bash
# Просмотр логов Apache
tail -f /var/log/apache2/error.log

# Просмотр активных сессий
mysql -u warranty_user -p warranty_saas -e "SELECT * FROM sessions;"

# Просмотр пользователей
mysql -u warranty_user -p warranty_saas -e "SELECT id, email, status FROM users;"

# Просмотр заявок
mysql -u warranty_user -p warranty_saas -e "SELECT COUNT(*) FROM form_submissions;"

# Очистка старых сессий
mysql -u warranty_user -p warranty_saas -e "DELETE FROM sessions WHERE UNIX_TIMESTAMP() - last_activity > 86400;"

# Бэкап БД
mysqldump -u warranty_user -p warranty_saas > backup_$(date +%Y%m%d).sql

# Восстановление из бэкапа
mysql -u warranty_user -p warranty_saas < backup_20251031.sql
```

---

## 🔒 Безопасность

```bash
# Проверка прав
find /var/www/html/warranty-saas -type f -exec chmod 644 {} \;
find /var/www/html/warranty-saas -type d -exec chmod 755 {} \;
chmod 775 /var/www/html/warranty-saas/uploads

# Защита config.php (через .htaccess уже защищен)
chmod 600 /var/www/html/warranty-saas/config.php

# Проверка SSL
openssl s_client -connect yourservice.com:443 -servername yourservice.com

# Автообновление SSL
systemctl status certbot.timer
```

---

## 📧 Email настройка (SMTP)

Добавьте в `config.php`:

```php
// После функции sendEmail() замените на:
function sendEmail($to, $subject, $message, $from = null) {
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';
    
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
        $mail->CharSet = 'UTF-8';
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

## 🎯 Быстрый тест

```bash
# Создание тестового пользователя через SQL
mysql -u warranty_user -p warranty_saas <<EOF
INSERT INTO users (email, password, role, subdomain, company_name, status) 
VALUES (
    'test@test.com',
    '\$2y\$10\$YourHashedPasswordHere',
    'client',
    'test',
    'Test Company',
    'approved'
);
EOF

# Проверка
curl -I https://test.yourservice.com
```

---

## 📱 Мониторинг

### Uptime Monitoring (бесплатно):
- UptimeRobot.com
- Pingdom.com
- StatusCake.com

### Проверка места на диске:
```bash
df -h
```

### Проверка памяти:
```bash
free -h
```

### Проверка процессов:
```bash
top
```

---

## 🔄 Автоматический бэкап

Создайте скрипт:
```bash
nano /root/backup_warranty.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/root/backups"
mkdir -p $BACKUP_DIR

# Бэкап БД
mysqldump -u warranty_user -pВАШ_ПАРОЛЬ warranty_saas > $BACKUP_DIR/db_$DATE.sql

# Бэкап файлов
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/warranty-saas

# Удаление старых бэкапов (старше 7 дней)
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

Права:
```bash
chmod +x /root/backup_warranty.sh
```

Автозапуск (каждый день в 3:00):
```bash
crontab -e
```

Добавьте:
```
0 3 * * * /root/backup_warranty.sh >> /root/backup.log 2>&1
```

---

## 📞 Что-то не работает?

1. **Проверьте логи**: `tail -f /var/log/apache2/error.log`
2. **Включите debug**: `define('DEBUG_MODE', true);` в config.php
3. **Проверьте права**: `ls -la /var/www/html/warranty-saas`
4. **Проверьте DNS**: `nslookup yourservice.com`
5. **Проверьте БД**: `mysql -u warranty_user -p warranty_saas`

Подробнее: [QUICKSTART.md](QUICKSTART.md)

---

✅ **Всё работает?** Поздравляем! 🎉

📖 Документация:
- [QUICKSTART.md](QUICKSTART.md) - подробная установка
- [README.md](README.md) - обзор возможностей
- [INSTALLATION.md](INSTALLATION.md) - детальная инструкция
