# 🔧 Исправление ошибки импорта database.sql

## ❌ Проблема

При повторном импорте `database.sql` возникали ошибки:

```
#1304 - PROCEDURE cleanup_expired_sessions already exists
Unrecognized statement type. (near "END" at position 101)
```

## ✅ Исправление

База данных теперь поддерживает **безопасный переимпорт** без ошибок.

### Что изменено:

1. **Добавлены проверки существования**
   ```sql
   DROP PROCEDURE IF EXISTS cleanup_expired_sessions$$
   DROP TRIGGER IF EXISTS after_user_insert$$
   DROP VIEW IF EXISTS user_stats;
   ```

2. **Добавлено безопасное удаление таблиц**
   ```sql
   SET FOREIGN_KEY_CHECKS = 0;
   
   DROP TABLE IF EXISTS `activity_logs`;
   DROP TABLE IF EXISTS `sessions`;
   DROP TABLE IF EXISTS `form_submissions`;
   DROP TABLE IF EXISTS `form_integrations`;
   DROP TABLE IF EXISTS `form_design`;
   DROP TABLE IF EXISTS `users`;
   
   SET FOREIGN_KEY_CHECKS = 1;
   ```

3. **EVENT уже использовал правильный синтаксис**
   ```sql
   CREATE EVENT IF NOT EXISTS cleanup_sessions_event
   ```

---

## 🚀 Теперь можно:

### ✅ Импортировать базу данных повторно
```bash
mysql -u warranty_user -p warranty_saas < database.sql
```

### ✅ Переустановить систему
```bash
# Очистить и переимпортировать
mysql -u root -p -e "DROP DATABASE IF EXISTS warranty_saas;"
mysql -u root -p -e "CREATE DATABASE warranty_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u warranty_user -p warranty_saas < database.sql
```

### ✅ Обновить структуру базы
Просто импортируйте снова - старые объекты будут удалены и созданы заново.

---

## 📋 Что происходит при импорте:

1. **Создаётся база** (если не существует)
2. **Отключаются проверки внешних ключей** (для безопасного удаления)
3. **Удаляются все старые таблицы** (если существуют)
4. **Включаются проверки обратно**
5. **Создаются все таблицы заново**
6. **Удаляются старые триггеры/процедуры/представления** (если существуют)
7. **Создаются новые объекты**
8. **Вставляются данные** (администратор, демо-пользователь)

---

## ⚠️ Важно

При переимпорте:
- ✅ Структура базы будет обновлена
- ⚠️ **Все данные будут удалены!**
- ⚠️ Все пользователи будут удалены
- ⚠️ Все заявки будут удалены
- ⚠️ Все настройки будут сброшены

**Создавайте бэкапы перед переимпортом!**

---

## 💾 Бэкап перед переимпортом

```bash
# Создать бэкап
mysqldump -u warranty_user -p warranty_saas > backup_$(date +%Y%m%d_%H%M%S).sql

# Переимпортировать
mysql -u warranty_user -p warranty_saas < database.sql

# Восстановить из бэкапа (если что-то пошло не так)
mysql -u warranty_user -p warranty_saas < backup_20251031_123456.sql
```

---

## 🐛 Если всё ещё есть ошибки

### Проблема: "DELIMITER command not supported"

**Решение 1**: Импортируйте через командную строку (не phpMyAdmin)
```bash
mysql -u warranty_user -p warranty_saas < database.sql
```

**Решение 2**: Если используете phpMyAdmin:
- Разделите `database.sql` на части
- Импортируйте таблицы отдельно от процедур/триггеров

---

### Проблема: "FOREIGN KEY constraint fails"

**Решение**: База теперь автоматически отключает проверки при импорте, но если ошибка всё равно есть:

```sql
SET FOREIGN_KEY_CHECKS = 0;
SOURCE /path/to/database.sql;
SET FOREIGN_KEY_CHECKS = 1;
```

---

### Проблема: "Access denied"

**Решение**: Проверьте права пользователя:

```sql
GRANT ALL PRIVILEGES ON warranty_saas.* TO 'warranty_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## ✅ Проверка успешного импорта

```bash
# Проверить таблицы
mysql -u warranty_user -p warranty_saas -e "SHOW TABLES;"

# Должно быть 6 таблиц:
# - users
# - form_design
# - form_integrations
# - form_submissions
# - sessions
# - activity_logs

# Проверить администратора
mysql -u warranty_user -p warranty_saas -e "SELECT email, role FROM users WHERE role='admin';"

# Должен быть: sundoze87@gmail.com
```

---

## 📚 Дополнительно

Если нужно только обновить структуру без удаления данных, используйте миграции вместо переимпорта.

---

**Проблема исправлена! Теперь database.sql можно безопасно импортировать повторно.** ✅
