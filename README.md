# Warranty Activation Page / Страница активации гарантии

This is a warranty activation form page with **Apple Liquid Glass Design** inspired by https://i-laminat.ru/garantia/

## Files / Файлы

- **warranty.html** - Main HTML page with the warranty activation form
- **styles.css** - Complete styling with Apple liquid glass (glassmorphism) design
- **script.js** - Interactive functionality (star rating, multi-select, form validation)
- **send-warranty.php** - PHP script for sending form data to email

## Features / Функции

### 1. **Multi-step form** with 6 steps:
   - Step 1: Customer identification (phone or contract number)
   - Step 2: Additional work not in contract (with dynamic field addition)
   - Step 3: Sales staff rating (5-star system)
   - Step 4: Delivery rating (5-star system)
   - Step 5: Installation rating (5-star system)
   - Step 6: Discount reservations - **Multi-select checkboxes** for products

### 2. **Apple Liquid Glass Design**:
   - Frosted glass effect with backdrop blur
   - Gradient purple background
   - Glassmorphism UI elements
   - Smooth animations and transitions
   - Beautiful hover effects
   - Premium look and feel

### 3. **Interactive elements**:
   - Contrasting star rating with tooltips (text shows ABOVE star on hover)
   - Yandex review invitation appears ONLY for 5-star ratings
   - Multi-select for discount products (Клей, Плинтус, Подложка, Грунтовка, Укладка)
   - "Nothing needed" option that deselects all products
   - Visual checkmark indicators on selected items
   - Dynamic textarea addition/removal
   - Privacy consent checkbox (required)
   - Form validation with error messages
   - Step completion indicators

### 4. **Privacy & Legal**:
   - Required consent checkbox before submission
   - Links to Privacy Policy and Data Processing Agreement
   - Form won't submit without consent

### 5. **Email Integration**:
   - Form data sent to email via PHP
   - Beautiful HTML email template with all information
   - Includes ratings, comments, selected discounts
   - Timestamp and IP address tracking

### 6. **Responsive design** - Optimized for both desktop and mobile devices
   - Mobile: Stars displayed vertically with text always visible
   - Mobile: Product cards in 2 columns (1 column on very small screens)
   - Full-width buttons on mobile

## Setup / Установка

### 1. Configure Email (Настройка почты)

Open `send-warranty.php` and change the email address:

```php
$to_email = "your-email@example.com"; // ЗАМЕНИТЕ НА ВАШУ ПОЧТУ!
```

### 2. Server Requirements (Требования к серверу)

- PHP 7.0 or higher
- Mail function enabled (or SMTP configured)
- Web server (Apache, Nginx, etc.)

### 3. Upload Files (Загрузка файлов)

Upload all files to your web server:
- warranty.html
- styles.css
- script.js
- send-warranty.php

### 4. Test (Тестирование)

1. Open warranty.html in browser
2. Fill out the form
3. Check that email arrives at configured address

## Email Configuration / Настройка почты

### Option 1: Using PHP mail() function (default)
Works on most shared hosting. No additional configuration needed.

### Option 2: Using SMTP (recommended for better delivery)

If you want to use SMTP (Gmail, SendGrid, etc.), replace the mail() function in `send-warranty.php` with PHPMailer:

```php
// Install PHPMailer: composer require phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->setFrom('noreply@yourdomain.com', 'Форма гарантии');
$mail->addAddress($to_email);
$mail->isHTML(true);
$mail->Subject = $subject;
$mail->Body = $message;
$mail->send();
```

## Customization / Настройка

- **Colors**: Edit `styles.css` (primary: #BF081A, secondary: #2f6f30)
- **Glass blur intensity**: Adjust `backdrop-filter: blur()` values
- **Form fields**: Modify `warranty.html`
- **Email template**: Edit HTML in `send-warranty.php`
- **Validation rules**: Adjust in `script.js`

## Email Template Features / Особенности шаблона письма

The email includes:
- ✅ All form data organized in sections
- ⭐ Visual star ratings
- 💬 Customer feedback and comments
- 🏷️ Selected discount items
- ✓ Consent status
- 🕐 Timestamp and IP address

## Security Notes / Безопасность

- All inputs are sanitized with `htmlspecialchars()`
- CSRF protection recommended for production
- Consider adding reCAPTCHA to prevent spam
- Use HTTPS in production

## Browser Support / Поддержка браузеров

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## License / Лицензия

Free to use and modify.
