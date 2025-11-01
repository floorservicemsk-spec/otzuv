<?php
/**
 * Скрипт для установки email и пароля администратора
 * Запустите ОДИН РАЗ после установки: php setup_admin.php
 */

// Ваши данные
$admin_email = 'sundoze87@gmail.com';
$admin_password = 'nifrit2303!@#';

// Генерация хеша
$password_hash = password_hash($admin_password, PASSWORD_BCRYPT);

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║          Настройка администратора                        ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "Email: $admin_email\n";
echo "Password Hash: $password_hash\n\n";

echo "SQL для обновления:\n";
echo "─────────────────────────────────────────────────────────\n";
echo "UPDATE users \n";
echo "SET email = '$admin_email', \n";
echo "    password = '$password_hash' \n";
echo "WHERE role = 'admin';\n";
echo "─────────────────────────────────────────────────────────\n\n";

echo "Или выполните автоматически:\n";
echo "mysql -u warranty_user -p warranty_saas -e \"UPDATE users SET email='$admin_email', password='$password_hash' WHERE role='admin';\"\n\n";

echo "✅ Готово! Теперь вы можете войти с новыми данными.\n";
?>
