# 🎯 НАЧНИТЕ ЗДЕСЬ

Добро пожаловать в **Warranty SaaS**! Этот файл поможет вам быстро разобраться.

---

## 📦 Что вы получили?

**Полнофункциональный SaaS-сервис** для управления гарантийными формами с:

✅ **Мультитенантностью** - каждый клиент на своем поддомене  
✅ **Персонализацией** - логотип, цвета, дизайн  
✅ **Интеграциями** - Email, Telegram, Google Sheets  
✅ **Админ-панелью** - управление пользователями  
✅ **Безопасностью** - CSRF, XSS, SQL injection защита  
✅ **Apple-дизайном** - минималистичный и элегантный  

---

## 🗂️ Структура файлов

```
saas/
├── 📚 START_HERE.md          ← ВЫ ЗДЕСЬ
├── 📚 QUICKSTART.md          ← Пошаговая установка (ЧИТАТЬ СНАЧАЛА!)
├── 📚 CHEATSHEET.md          ← Шпаргалка с командами
├── 📚 README.md              ← Обзор возможностей
├── 📚 INSTALLATION.md        ← Детальная инструкция
├── 📚 PROJECT_SUMMARY.md     ← Полная сводка проекта
│
├── 🗄️ database.sql           ← Схема базы данных
├── ⚙️ config.php              ← Конфигурация (настроить!)
│
├── 🔐 login.php              ← Авторизация
├── 📝 register.php           ← Регистрация
├── 📋 form.php               ← Динамическая форма
├── 🔌 widget.js              ← Виджет для сайтов
│
├── dashboard/                ← Панель клиента
│   ├── index.php            - Главная
│   ├── design.php           - Настройки дизайна
│   ├── integrations.php     - Интеграции
│   └── submissions.php      - Просмотр заявок
│
├── admin/                    ← Админ-панель
│   ├── dashboard.php        - Главная админа
│   └── users.php            - Управление пользователями
│
├── api/                      ← API endpoints
│   └── submit.php           - Обработка заявок
│
└── assets/                   ← CSS, JS, загрузки
```

---

## 🚀 Быстрый старт (3 шага)

### 1️⃣ Выберите ваш сценарий:

📍 **Тестирую локально** → [QUICKSTART.md → Сценарий A](QUICKSTART.md#сценарий-a-локальный-сервер)

📍 **Запускаю на VPS** → [QUICKSTART.md → Сценарий B](QUICKSTART.md#сценарий-b-vps-сервер-production)

📍 **Хостинг с cPanel** → [QUICKSTART.md → Сценарий C](QUICKSTART.md#сценарий-c-shared-хостинг)

### 2️⃣ Следуйте инструкции

Откройте [QUICKSTART.md](QUICKSTART.md) и выполните все шаги.

**Время установки:**
- Локально: ~15 минут
- VPS: ~30 минут
- Хостинг: ~20 минут

### 3️⃣ Первый вход

После установки откройте:
```
https://yourservice.com/login.php

Email: sundoze87@gmail.com
Password: nifrit2303!@#
```

⚠️ **ВАЖНО**: После импорта database.sql выполните `php setup_admin.php` (см. [ADMIN_SETUP.md](ADMIN_SETUP.md))

---

## 📖 Что читать дальше?

### Для начинающих:
1. **START_HERE.md** ← вы здесь
2. **[QUICKSTART.md](QUICKSTART.md)** - пошаговая установка
3. **[CHEATSHEET.md](CHEATSHEET.md)** - команды и шпаргалка

### Для продвинутых:
4. **[README.md](README.md)** - обзор всех возможностей
5. **[INSTALLATION.md](INSTALLATION.md)** - детальная настройка
6. **[PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)** - архитектура

---

## ⚡ Супер-быстрая установка (Ubuntu)

Если у вас Ubuntu VPS и вы хотите запустить прямо сейчас:

```bash
# 1. Установите зависимости
sudo apt update && sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-curl git -y

# 2. Клонируйте репозиторий
cd /var/www/html
sudo git clone YOUR_REPO_URL warranty-saas
cd warranty-saas

# 3. Настройте БД
sudo mysql <<EOF
CREATE DATABASE warranty_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'warranty_user'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD';
GRANT ALL PRIVILEGES ON warranty_saas.* TO 'warranty_user'@'localhost';
FLUSH PRIVILEGES;
EOF

sudo mysql -u warranty_user -pYOUR_PASSWORD warranty_saas < database.sql

# 4. Настройте config.php
sudo nano config.php
# Измените: DB_PASS, MAIN_DOMAIN, SECRET_KEY

# 5. Установите права
sudo chown -R www-data:www-data .
sudo chmod -R 755 .

# 6. Настройте Apache
sudo nano /etc/apache2/sites-available/warranty.conf
# (скопируйте конфиг из QUICKSTART.md)

sudo a2ensite warranty.conf
sudo a2enmod rewrite
sudo systemctl restart apache2

# 7. Готово!
```

Откройте: `http://ваш_IP/login.php`

---

## 🎯 Минимальные требования

✅ **PHP** 7.4 или выше  
✅ **MySQL** 5.7 или выше  
✅ **Apache/Nginx** с mod_rewrite  
✅ **Домен** с wildcard DNS  
✅ **SSL** сертификат (рекомендуется)  

---

## 🔑 Дефолтные данные

### Администратор:
```
Email: admin@yourservice.com
Password: admin123
```

### База данных:
```
Database: warranty_saas
User: warranty_user (создается вручную)
Tables: 6 (users, form_design, form_integrations, form_submissions, sessions, activity_logs)
```

---

## 🎨 Что можно настроить?

### Для клиентов (через Dashboard):
- 🖼️ **Логотип** - загрузите свой
- 🎨 **Цвета кнопок** - любой цвет
- 🌈 **Градиент фона** - 3 цвета
- 📧 **Email** - куда приходят заявки
- 💬 **Telegram** - уведомления в канал
- 📊 **Google Sheets** - автоматическое заполнение

### Для администратора:
- ✅ **Апрув пользователей** - вручную или авто
- 🚫 **Блокировка** - временная или постоянная
- 📊 **Мониторинг** - статистика и логи
- ⚙️ **Системные настройки** - в config.php

---

## 📞 Частые вопросы

### Как добавить клиента?

1. Клиент регистрируется: `/register.php`
2. Администратор одобряет: `/admin/users.php`
3. Клиент получает email с доступом
4. Клиент настраивает форму в Dashboard

### Как работают поддомены?

```
yourservice.com           - главный сайт (админка)
client1.yourservice.com   - форма клиента 1
client2.yourservice.com   - форма клиента 2
test.yourservice.com      - форма тестового клиента
```

### Как встроить форму на сайт?

Скопируйте код виджета из Dashboard:
```html
<script src="https://client1.yourservice.com/widget.js"></script>
```

Вставьте на любую страницу вашего сайта.

### Как изменить цвета?

Dashboard → Дизайн → выберите цвета → Сохранить

### Куда приходят заявки?

- **В базу данных** - всегда
- **На Email** - если настроено
- **В Telegram** - если настроено
- **В Google Sheets** - если настроено

### Как сделать бэкап?

```bash
# База данных
mysqldump -u warranty_user -p warranty_saas > backup.sql

# Файлы
tar -czf backup.tar.gz /var/www/html/warranty-saas
```

---

## 🐛 Что-то не работает?

### Шаг 1: Проверьте логи
```bash
tail -f /var/log/apache2/error.log
```

### Шаг 2: Включите debug
В `config.php`:
```php
define('DEBUG_MODE', true);
```

### Шаг 3: Проверьте требования
```bash
php -v        # Должно быть 7.4+
mysql --version  # 5.7+
```

### Шаг 4: Читайте документацию
[QUICKSTART.md → Устранение проблем](QUICKSTART.md#🔧-устранение-проблем)

---

## ✅ Чек-лист запуска

Перед запуском в production:

- [ ] База данных создана и импортирована
- [ ] `config.php` настроен (DB, DOMAIN, SECRET_KEY)
- [ ] Права доступа установлены (755/644)
- [ ] Apache VirtualHost настроен
- [ ] DNS настроены (wildcard A-запись)
- [ ] SSL сертификат установлен
- [ ] Firewall настроен (порты 80, 443)
- [ ] Пароль администратора изменён
- [ ] Email отправка работает (тест)
- [ ] Создан тестовый пользователь
- [ ] Форма открывается на поддомене
- [ ] Виджет работает
- [ ] Бэкапы настроены

---

## 🎓 Обучение

### Для пользователей:
1. Зарегистрируйтесь на сайте
2. Дождитесь одобрения администратора
3. Войдите в Dashboard
4. Загрузите логотип в разделе "Дизайн"
5. Настройте цвета
6. Настройте интеграции (Email/Telegram/Sheets)
7. Получите ссылку на форму
8. Встройте виджет на сайт

### Для администраторов:
1. Войдите: `/login.php`
2. Перейдите: `/admin/dashboard.php`
3. Одобряйте новых пользователей
4. Мониторьте статистику
5. Просматривайте логи активности
6. Блокируйте нарушителей при необходимости

---

## 🚀 Что дальше?

После успешной установки:

1. **Настройте Email** - для уведомлений
2. **Настройте бэкапы** - автоматические
3. **Настройте мониторинг** - UptimeRobot
4. **Пригласите клиентов** - первые 5 бесплатно
5. **Соберите feedback** - улучшайте сервис

---

## 📚 Полезные ссылки

- **[QUICKSTART.md](QUICKSTART.md)** - 📍 **НАЧНИТЕ С ЭТОГО!**
- [CHEATSHEET.md](CHEATSHEET.md) - команды и шпаргалка
- [README.md](README.md) - обзор возможностей
- [INSTALLATION.md](INSTALLATION.md) - детальная установка
- [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - архитектура

### Оригинальные инструкции:
- [TELEGRAM_SETUP.md](TELEGRAM_SETUP.md) - настройка Telegram
- [GOOGLE_SHEETS_SETUP.md](GOOGLE_SHEETS_SETUP.md) - настройка Sheets
- [WIDGET_SETUP.md](WIDGET_SETUP.md) - настройка виджета

---

## 💡 Совет

**Начните с локальной установки** для тестирования, затем переносите на production сервер.

Это позволит:
- Понять как работает система
- Протестировать функционал
- Избежать ошибок на production
- Настроить всё правильно

---

## 🎉 Готовы начать?

1. 📖 Откройте [QUICKSTART.md](QUICKSTART.md)
2. 🎯 Выберите ваш сценарий (A/B/C)
3. 🚀 Следуйте инструкциям
4. ✅ Запустите сервис!

**Время установки: 15-30 минут**

---

# 🔥 ПОЕХАЛИ!

👉 **Следующий шаг: откройте [QUICKSTART.md](QUICKSTART.md)**

---

*Создано с ❤️ для управления гарантиями*

**Версия: 1.0.0** | Дата: 31.10.2025
