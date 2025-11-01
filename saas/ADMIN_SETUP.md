# 🔑 Настройка учетных данных администратора

## Ваши данные администратора:

```
Email: sundoze87@gmail.com
Password: nifrit2303!@#
```

---

## ⚡ Быстрая установка (выберите способ)

### Способ 1: Автоматический (рекомендуется)

После импорта `database.sql` выполните:

```bash
cd /var/www/html/warranty-saas  # или путь к вашим файлам
php setup_admin.php
```

Скрипт покажет SQL команду - выполните её в MySQL.

---

### Способ 2: Вручную через MySQL

1. Сгенерируйте хеш пароля:

```bash
php -r "echo password_hash('nifrit2303!@#', PASSWORD_BCRYPT) . PHP_EOL;"
```

2. Скопируйте полученный хеш

3. Выполните в MySQL:

```sql
UPDATE users 
SET email = 'sundoze87@gmail.com', 
    password = 'ВСТАВЬТЕ_СЮДА_ХЕШ_ИЗ_ШАГА_1' 
WHERE role = 'admin';
```

---

### Способ 3: Через SQL сразу

Выполните эту команду **ПОСЛЕ** импорта database.sql:

```bash
# Сгенерируем хеш и сразу обновим
HASH=$(php -r "echo password_hash('nifrit2303!@#', PASSWORD_BCRYPT);")

mysql -u warranty_user -p warranty_saas <<EOF
UPDATE users 
SET email = 'sundoze87@gmail.com', 
    password = '$HASH' 
WHERE role = 'admin';
EOF
```

---

### Способ 4: Через phpMyAdmin

1. Откройте phpMyAdmin
2. Выберите базу `warranty_saas`
3. Откройте таблицу `users`
4. Найдите запись с `role = 'admin'`
5. Нажмите "Редактировать"
6. Измените:
   - `email` → `sundoze87@gmail.com`
   - `password` → выполните в SQL вкладке:
     ```sql
     SELECT PASSWORD('nifrit2303!@#');
     ```
     Или используйте онлайн bcrypt генератор: https://bcrypt-generator.com/
     Вставьте rounds: 10, password: nifrit2303!@#
7. Сохраните

---

## ✅ Проверка

После установки войдите:

```
URL: https://yourservice.com/login.php
Email: sundoze87@gmail.com
Password: nifrit2303!@#
```

---

## 🔒 Безопасность

⚠️ **ВАЖНО:**
- Хеш пароля в `database.sql` - это placeholder
- Реальный хеш будет создан при выполнении одного из способов выше
- Никогда не храните пароли в открытом виде
- После первого входа можете сменить пароль через админ-панель

---

## 🐛 Если не получается войти

1. Проверьте что выполнили UPDATE команду
2. Проверьте email в базе:
   ```sql
   SELECT email FROM users WHERE role = 'admin';
   ```
3. Проверьте что хеш не пустой:
   ```sql
   SELECT LENGTH(password) FROM users WHERE role = 'admin';
   ```
   Должно быть 60 символов

4. Попробуйте сбросить пароль:
   ```bash
   php -r "echo password_hash('nifrit2303!@#', PASSWORD_BCRYPT);"
   # Скопируйте результат и обновите через SQL
   ```

---

## 📞 Альтернативные решения

Если PHP не установлен на вашем компьютере:

1. Используйте онлайн bcrypt генератор:
   - https://bcrypt-generator.com/
   - https://www.browserling.com/tools/bcrypt
   
2. Вставьте пароль: `nifrit2303!@#`
3. Rounds: `10`
4. Скопируйте хеш
5. Обновите через SQL

---

Готово! 🎉
