# 🛡️ Warranty SaaS

Полнофункциональная SaaS-платформа для управления формами активации гарантии с мультитенантностью и Apple-стайл дизайном.

---

## ✨ Возможности

### 🎨 Для клиентов
- **Персонализация дизайна**: загрузка логотипа, настройка цветов кнопок и фона
- **Интеграции**: Email, Telegram, Google Sheets
- **Уникальный поддомен**: каждый клиент получает свой `subdomain.yourservice.com`
- **Просмотр заявок**: таблица с фильтрацией и экспортом в CSV
- **Виджет для сайта**: простое встраивание формы на любую страницу
- **Статистика**: количество заявок, последняя активность

### 🔐 Для администраторов
- **Управление пользователями**: апрув регистраций, блокировка/разблокировка
- **Мониторинг активности**: полный лог действий пользователей
- **Email-уведомления**: автоматическая отправка при апруве/отклонении
- **Статистика системы**: общее количество клиентов, заявок
- **Безопасность**: CSRF-защита, хеширование паролей, сессии

### 📋 Форма активации гарантии
- **Многошаговая**: телефон, имя, email, 3 рейтинга
- **Маска ввода телефона**: +7 (___) ___-__-__
- **Валидация**: проверка заполнения всех полей
- **Рейтинги звездами**: оценка продаж, доставки, монтажа
- **Адаптивный дизайн**: работает на всех устройствах
- **Персонализация**: логотип и цвета клиента

---

## 🏗️ Архитектура

### Технологии
- **Backend**: PHP 7.4+, MySQL 5.7+
- **Frontend**: Vanilla JavaScript, CSS3
- **Стиль**: Apple Human Interface Guidelines
- **Безопасность**: CSRF tokens, prepared statements, password hashing

### Структура базы данных
```
users                   # Пользователи и их роли
├─ form_design          # Настройки дизайна (логотип, цвета)
├─ form_integrations    # Настройки интеграций (Email, Telegram, Sheets)
└─ form_submissions     # Заявки от клиентов

sessions                # Сессии пользователей
activity_logs           # Логи активности
```

### Роли
- **Administrator**: полный доступ к системе, апрув пользователей
- **Client**: доступ к своей админ-панели и настройкам формы

---

## 📦 Установка

Подробная инструкция: [INSTALLATION.md](INSTALLATION.md)

### Быстрый старт

1. **Клонируйте репозиторий**
```bash
git clone https://github.com/yourusername/warranty-saas.git
cd warranty-saas/saas
```

2. **Создайте базу данных**
```bash
mysql -u root -p -e "CREATE DATABASE warranty_saas;"
mysql -u root -p warranty_saas < database.sql
```

3. **Настройте config.php**
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'warranty_saas');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('MAIN_DOMAIN', 'yourservice.com');
```

4. **Настройте Apache/Nginx для поддоменов**
```apache
ServerAlias *.yourservice.com
```

5. **Настройте DNS (wildcard)**
```
A    *.yourservice.com    -> YOUR_IP
```

6. **Войдите как администратор**
```
URL: https://yourservice.com/login.php
Email: admin@yourservice.com
Password: admin123
```

---

## 🚀 Использование

### Для администратора

1. Войдите в админ-панель: `/admin/dashboard.php`
2. Ожидайте регистрации новых пользователей
3. Одобряйте или отклоняйте заявки в разделе "Пользователи"
4. Просматривайте статистику и активность

### Для клиента

1. Зарегистрируйтесь: `/register.php`
2. Ожидайте подтверждения администратора
3. После одобрения войдите: `/login.php`
4. Настройте дизайн: `/dashboard/design.php`
   - Загрузите логотип
   - Выберите цвета кнопок и фона
5. Настройте интеграции: `/dashboard/integrations.php`
   - Email: укажите адрес для уведомлений
   - Telegram: подключите бота
   - Google Sheets: настройте таблицу
6. Получите ссылку на форму: `https://yoursubdomain.yourservice.com`
7. Встройте виджет на сайт:
```html
<script src="https://yoursubdomain.yourservice.com/widget.js"></script>
```

---

## 📚 Документация

- [📦 Установка](INSTALLATION.md) - полная инструкция по установке
- [💬 Настройка Telegram](TELEGRAM_SETUP.md) - подключение Telegram-бота
- [📊 Настройка Google Sheets](GOOGLE_SHEETS_SETUP.md) - интеграция с таблицами
- [🔌 Виджет](WIDGET_SETUP.md) - встраивание формы на сайт

---

## 🎨 Скриншоты

### Дашборд клиента
![Dashboard](screenshots/dashboard.png)

### Настройки дизайна
![Design Settings](screenshots/design.png)

### Форма активации
![Warranty Form](screenshots/form.png)

### Админ-панель
![Admin Panel](screenshots/admin.png)

---

## 🔒 Безопасность

- ✅ CSRF-защита на всех формах
- ✅ Prepared statements для защиты от SQL-инъекций
- ✅ Password hashing (bcrypt)
- ✅ Session management с таймаутом
- ✅ Activity logging
- ✅ File upload validation
- ✅ XSS protection (HTML escaping)

---

## 🛠️ Разработка

### Структура проекта
```
saas/
├── admin/                 # Админ-панель
│   ├── dashboard.php
│   ├── users.php
│   └── includes/
├── dashboard/             # Панель клиента
│   ├── index.php
│   ├── design.php
│   ├── integrations.php
│   ├── submissions.php
│   └── includes/
├── api/                   # API endpoints
│   └── submit.php
├── assets/
│   ├── css/              # Стили
│   └── js/               # JavaScript
├── uploads/              # Загруженные файлы
├── config.php            # Конфигурация
├── database.sql          # Схема БД
├── login.php             # Авторизация
├── register.php          # Регистрация
├── form.php              # Динамическая форма
├── widget.js             # Виджет для встраивания
└── README.md
```

### Добавление новых функций

1. **Новая таблица БД**: обновите `database.sql`
2. **Новая страница**: создайте в `dashboard/` или `admin/`
3. **API endpoint**: добавьте в `api/`
4. **Стили**: используйте переменные из `dashboard.css`

---

## 🤝 Вклад в проект

Мы приветствуем вклад в проект! Пожалуйста:

1. Форкните репозиторий
2. Создайте ветку: `git checkout -b feature/amazing-feature`
3. Закоммитьте изменения: `git commit -m 'Add amazing feature'`
4. Запушьте: `git push origin feature/amazing-feature`
5. Откройте Pull Request

---

## 📄 Лицензия

MIT License - см. файл [LICENSE](LICENSE)

---

## 💼 Поддержка

- 📧 Email: support@yourservice.com
- 💬 Telegram: [@yoursupport](https://t.me/yoursupport)
- 🐛 Issues: [GitHub Issues](https://github.com/yourusername/warranty-saas/issues)

---

## 🎯 Roadmap

- [ ] Экспорт заявок в Excel
- [ ] SMS-уведомления
- [ ] Webhook интеграции
- [ ] Multi-language support
- [ ] API для внешних сервисов
- [ ] Аналитика и дашборды
- [ ] White-label решение
- [ ] Mobile app

---

## 🙏 Благодарности

- [IMask.js](https://imask.js.org/) - маска для телефона
- [Apple Design Resources](https://developer.apple.com/design/) - дизайн-гайдлайны
- PHP Community - за awesome инструменты

---

**Сделано с ❤️ для управления гарантиями**

Версия: 1.0.0 | Последнее обновление: 2025-10-31
