# 🔑 Учетные данные администратора

## ✅ Ваши данные для входа

```
Email:    sundoze87@gmail.com
Password: nifrit2303!@#
```

---

## 📋 Что нужно сделать после установки

### После импорта `database.sql`:

**Вариант 1: Автоматически (рекомендуется)**
```bash
cd /var/www/html/warranty-saas  # или ваш путь к файлам
php setup_admin.php
```

Скопируйте и выполните показанную SQL команду.

---

**Вариант 2: Одной командой**
```bash
HASH=$(php -r "echo password_hash('nifrit2303!@#', PASSWORD_BCRYPT);")
mysql -u warranty_user -p warranty_saas -e "UPDATE users SET email='sundoze87@gmail.com', password='$HASH' WHERE role='admin';"
```

---

**Вариант 3: Вручную через SQL**

1. Сгенерируйте хеш:
```bash
php -r "echo password_hash('nifrit2303!@#', PASSWORD_BCRYPT);"
```

2. Обновите в MySQL:
```sql
UPDATE users 
SET email = 'sundoze87@gmail.com', 
    password = 'ВСТАВЬТЕ_ХЕШ_СЮДА' 
WHERE role = 'admin';
```

---

**Вариант 4: Через phpMyAdmin**

1. Откройте https://bcrypt-generator.com/
2. Password: `nifrit2303!@#`, Rounds: `10`
3. Скопируйте хеш
4. В phpMyAdmin → `warranty_saas` → `users` → Редактировать admin
5. Вставьте email и хеш пароля

---

## 🌐 Вход в систему

После настройки откройте:

- **Локально**: `http://warranty.local/login.php`
- **VPS**: `https://yourservice.com/login.php`
- **Хостинг**: `https://yourservice.com/warranty-saas/login.php`

Используйте указанные выше email и пароль.

---

## 🔒 Безопасность

- ✅ Пароль хранится в БД в виде bcrypt хеша
- ✅ Placeholder в `database.sql` будет заменён реальным хешем
- ✅ Никогда не храните пароли в открытом виде
- ⚠️ Если забыли пароль - используйте любой из вариантов выше для сброса

---

## 📚 Дополнительная информация

Подробные инструкции по установке:
- [ADMIN_SETUP.md](ADMIN_SETUP.md) - детальная настройка
- [QUICKSTART.md](QUICKSTART.md) - пошаговая установка
- [START_HERE.md](START_HERE.md) - с чего начать

---

**Важно**: Эти данные используются только вами. Храните их в безопасности! 🔐
