<?php
/**
 * Динамическая форма гарантии для клиентов
 * Доступна на поддоменах: subdomain.yourservice.com
 */
define('SAAS_SYSTEM', true);
require_once 'config.php';

// Определение пользователя по поддомену
$subdomain = getSubdomain();

if (empty($subdomain) || $subdomain === 'www') {
    die('Неверный поддомен');
}

// Получение пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE subdomain = ? AND status = 'approved'");
$stmt->execute([$subdomain]);
$user = $stmt->fetch();

if (!$user) {
    die('Пользователь не найден или не активирован');
}

// Получение настроек дизайна
$stmt = $pdo->prepare("SELECT * FROM form_design WHERE user_id = ?");
$stmt->execute([$user['id']]);
$design = $stmt->fetch();

// Получение настроек интеграций
$stmt = $pdo->prepare("SELECT * FROM form_integrations WHERE user_id = ?");
$stmt->execute([$user['id']]);
$integrations = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Активация гарантии - <?= h($user['company_name']) ?></title>
    
    <!-- Оригинальные стили из warranty.html -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/form.css">
    <script src="https://unpkg.com/imask"></script>
    
    <style>
        /* Динамические стили на основе настроек пользователя */
        body {
            background: linear-gradient(135deg, 
                <?= h($design['background_gradient_start']) ?> 0%, 
                <?= h($design['background_gradient_middle']) ?> 50%, 
                <?= h($design['background_gradient_end']) ?> 100%
            );
        }
        
        .btn {
            background: linear-gradient(135deg, <?= h($design['button_color']) ?> 0%, color-mix(in srgb, <?= h($design['button_color']) ?> 80%, black) 100%);
        }
        
        .primary-color {
            color: <?= h($design['primary_color']) ?>;
        }
        
        .form-logo {
            max-width: 200px;
            max-height: 100px;
            margin: 0 auto 24px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php if ($design['logo_url']): ?>
            <img src="<?= BASE_URL . h($design['logo_url']) ?>" alt="<?= h($user['company_name']) ?>" class="form-logo">
        <?php endif; ?>
        
        <h1>Активация гарантии</h1>
        <p class="subtitle"><?= h($user['company_name']) ?></p>
        
        <form id="warranty-form" data-user-id="<?= $user['id'] ?>">
            <!-- ШАГ 1: Номер телефона -->
            <div class="step" data-step="1">
                <div class="input">
                    <span>Ваш номер телефона</span>
                    <input type="text" name="phone" id="phone-input" placeholder="+7 (___) ___-__-__" data-step="1">
                </div>
            </div>
            
            <!-- ШАГ 2: Имя -->
            <div class="step" data-step="2">
                <div class="input">
                    <span>Ваше имя</span>
                    <input type="text" name="name" placeholder="Иван" data-step="2">
                </div>
            </div>
            
            <!-- ШАГ 3: Email -->
            <div class="step" data-step="3">
                <div class="input">
                    <span>Ваша электронная почта</span>
                    <input type="email" name="email" placeholder="example@mail.com" data-step="3">
                </div>
            </div>
            
            <!-- ШАГ 4: Оценка менеджера продаж -->
            <div class="step" data-step="4">
                <div class="input">
                    <span>Оцените работу менеджера продаж</span>
                    <div class="stars" data-rating-group="sales">
                        <div class="star" data-value="1"><span class="star-icon">★</span></div>
                        <div class="star" data-value="2"><span class="star-icon">★</span></div>
                        <div class="star" data-value="3"><span class="star-icon">★</span></div>
                        <div class="star" data-value="4"><span class="star-icon">★</span></div>
                        <div class="star" data-value="5"><span class="star-icon">★</span></div>
                    </div>
                    <input type="hidden" name="sales_rating" data-step="4">
                </div>
                <div class="stars-btns">
                    <button type="button" class="btn" onclick="submitRating('sales')">ОСТАВИТЬ ОТЗЫВ</button>
                </div>
            </div>
            
            <!-- ШАГ 5: Оценка доставки -->
            <div class="step" data-step="5">
                <div class="input">
                    <span>Оцените работу доставки</span>
                    <div class="stars" data-rating-group="delivery">
                        <div class="star" data-value="1"><span class="star-icon">★</span></div>
                        <div class="star" data-value="2"><span class="star-icon">★</span></div>
                        <div class="star" data-value="3"><span class="star-icon">★</span></div>
                        <div class="star" data-value="4"><span class="star-icon">★</span></div>
                        <div class="star" data-value="5"><span class="star-icon">★</span></div>
                    </div>
                    <input type="hidden" name="delivery_rating" data-step="5">
                </div>
                <div class="stars-btns">
                    <button type="button" class="btn" onclick="submitRating('delivery')">ОСТАВИТЬ ОТЗЫВ</button>
                </div>
            </div>
            
            <!-- ШАГ 6: Оценка монтажников -->
            <div class="step" data-step="6">
                <div class="input">
                    <span>Оцените работу монтажников</span>
                    <div class="stars" data-rating-group="installation">
                        <div class="star" data-value="1"><span class="star-icon">★</span></div>
                        <div class="star" data-value="2"><span class="star-icon">★</span></div>
                        <div class="star" data-value="3"><span class="star-icon">★</span></div>
                        <div class="star" data-value="4"><span class="star-icon">★</span></div>
                        <div class="star" data-value="5"><span class="star-icon">★</span></div>
                    </div>
                    <input type="hidden" name="installation_rating" data-step="6">
                </div>
                <div class="stars-btns">
                    <button type="button" class="btn" onclick="submitRating('installation')">ОСТАВИТЬ ОТЗЫВ</button>
                </div>
            </div>
            
            <!-- ШАГ 7: Согласие -->
            <div class="step" data-step="7">
                <div class="checkbox-wrapper">
                    <input type="checkbox" id="consent" name="consent">
                    <label for="consent">Даю согласие на обработку персональных данных</label>
                </div>
                <button type="submit" id="submit-btn" class="btn" disabled>АКТИВИРОВАТЬ ГАРАНТИЮ</button>
            </div>
        </form>
        
        <h1 class="end" style="display: none;">Спасибо! Гарантийный талон активирован</h1>
    </div>
    
    <script src="<?= BASE_URL ?>/assets/js/form.js"></script>
    <script>
        // Передача данных пользователя в JS
        window.SAAS_CONFIG = {
            userId: <?= $user['id'] ?>,
            apiUrl: '<?= BASE_URL ?>/api/submit.php'
        };
    </script>
</body>
</html>
