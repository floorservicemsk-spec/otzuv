<?php
/**
 * Динамическая форма гарантии (Версия 2.0 - с form_id)
 * URL: /form/{form_id}
 */
define('SAAS_SYSTEM', true);
require_once 'config.php';

// Получение form_id из URL
$form_id = $_GET['id'] ?? '';

if (empty($form_id)) {
    die('Неверная ссылка на форму');
}

// Получение пользователя по form_id
$stmt = $pdo->prepare("SELECT * FROM users WHERE form_id = ? AND status = 'approved'");
$stmt->execute([$form_id]);
$user = $stmt->fetch();

if (!$user) {
    die('Форма не найдена или не активирована');
}

// Получение настроек дизайна
$stmt = $pdo->prepare("SELECT * FROM form_design WHERE user_id = ?");
$stmt->execute([$user['id']]);
$design = $stmt->fetch();

// Получение полей формы
$stmt = $pdo->prepare("
    SELECT * FROM form_fields 
    WHERE user_id = ? AND is_enabled = 1 
    ORDER BY field_order ASC, id ASC
");
$stmt->execute([$user['id']]);
$form_fields = $stmt->fetchAll();

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
        
        .step {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .step.active {
            display: block;
            opacity: 1;
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
            <?php 
            $step_number = 1;
            foreach ($form_fields as $field): 
                $is_first_step = ($step_number === 1);
            ?>
                <div class="step <?= $is_first_step ? 'active' : '' ?>" data-step="<?= $step_number ?>">
                    <div class="input">
                        <span><?= h($field['field_label']) ?></span>
                        
                        <?php if ($field['field_type'] === 'rating'): ?>
                            <!-- Рейтинг звездами -->
                            <div class="stars" data-rating-group="<?= h($field['field_key']) ?>">
                                <div class="star" data-value="1"><span class="star-icon">★</span></div>
                                <div class="star" data-value="2"><span class="star-icon">★</span></div>
                                <div class="star" data-value="3"><span class="star-icon">★</span></div>
                                <div class="star" data-value="4"><span class="star-icon">★</span></div>
                                <div class="star" data-value="5"><span class="star-icon">★</span></div>
                            </div>
                            <input type="hidden" 
                                   name="<?= h($field['field_key']) ?>" 
                                   data-step="<?= $step_number ?>"
                                   <?= $field['is_required'] ? 'required' : '' ?>>
                            
                            <div class="stars-btns">
                                <button type="button" class="btn" onclick="submitStep(<?= $step_number ?>)">ПРОДОЛЖИТЬ</button>
                            </div>
                            
                        <?php elseif ($field['field_type'] === 'textarea'): ?>
                            <!-- Длинный текст -->
                            <textarea 
                                name="<?= h($field['field_key']) ?>" 
                                placeholder="<?= h($field['placeholder']) ?>"
                                data-step="<?= $step_number ?>"
                                rows="4"
                                <?= $field['is_required'] ? 'required' : '' ?>></textarea>
                                
                        <?php elseif ($field['field_type'] === 'checkbox'): ?>
                            <!-- Чекбокс -->
                            <div class="checkbox-wrapper">
                                <input type="checkbox" 
                                       id="field_<?= $field['id'] ?>"
                                       name="<?= h($field['field_key']) ?>" 
                                       data-step="<?= $step_number ?>"
                                       <?= $field['is_required'] ? 'required' : '' ?>>
                                <label for="field_<?= $field['id'] ?>"><?= h($field['placeholder'] ?: $field['field_label']) ?></label>
                            </div>
                            
                        <?php else: ?>
                            <!-- Обычные поля (text, email, tel, number) -->
                            <input type="<?= h($field['field_type']) ?>" 
                                   name="<?= h($field['field_key']) ?>" 
                                   placeholder="<?= h($field['placeholder']) ?>"
                                   data-step="<?= $step_number ?>"
                                   <?= $field['field_key'] === 'phone' ? 'id="phone-input"' : '' ?>
                                   <?= $field['is_required'] ? 'required' : '' ?>>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
                $step_number++;
            endforeach; 
            ?>
            
            <!-- Финальная кнопка отправки -->
            <div class="step" data-step="<?= $step_number ?>">
                <button type="submit" id="submit-btn" class="btn">ОТПРАВИТЬ</button>
            </div>
        </form>
        
        <h1 class="end" style="display: none;">Спасибо! Заявка отправлена</h1>
    </div>
    
    <script>
        // Передача данных в JS
        window.SAAS_CONFIG = {
            userId: <?= $user['id'] ?>,
            formId: '<?= h($form_id) ?>',
            apiUrl: '<?= BASE_URL ?>/api/submit_v2.php',
            totalSteps: <?= $step_number ?>
        };
        
        let currentStep = 1;
        
        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            initPhoneMask();
            initStarRatings();
            initFormNavigation();
        });
        
        // Маска для телефона
        function initPhoneMask() {
            const phoneInput = document.getElementById('phone-input');
            if (phoneInput) {
                IMask(phoneInput, {
                    mask: '+{7} (000) 000-00-00'
                });
                
                phoneInput.addEventListener('blur', function() {
                    if (this.value.includes('_') || this.value.length < 18) {
                        this.classList.add('error');
                    } else {
                        this.classList.remove('error');
                    }
                });
            }
        }
        
        // Рейтинги звездами
        function initStarRatings() {
            const starContainers = document.querySelectorAll('.stars');
            
            starContainers.forEach(container => {
                const stars = container.querySelectorAll('.star');
                const group = container.getAttribute('data-rating-group');
                
                stars.forEach((star, index) => {
                    star.addEventListener('mouseenter', function() {
                        stars.forEach((s, i) => {
                            if (i <= index) {
                                s.classList.add('hover-active');
                            } else {
                                s.classList.remove('hover-active');
                            }
                        });
                    });
                    
                    star.addEventListener('click', function() {
                        const value = parseInt(this.getAttribute('data-value'));
                        const hiddenInput = container.parentElement.querySelector('input[type="hidden"]');
                        hiddenInput.value = value;
                        
                        stars.forEach((s, i) => {
                            if (i < value) {
                                s.classList.add('active');
                            } else {
                                s.classList.remove('active');
                            }
                        });
                    });
                });
                
                container.addEventListener('mouseleave', function() {
                    stars.forEach(s => s.classList.remove('hover-active'));
                });
            });
        }
        
        // Навигация по шагам
        function initFormNavigation() {
            const form = document.getElementById('warranty-form');
            const steps = document.querySelectorAll('.step');
            
            steps.forEach(step => {
                const input = step.querySelector('input, textarea');
                if (input && input.type !== 'hidden' && input.type !== 'checkbox') {
                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const stepNum = parseInt(step.getAttribute('data-step'));
                            submitStep(stepNum);
                        }
                    });
                }
            });
        }
        
        // Переход к следующему шагу
        function submitStep(stepNum) {
            const currentStepEl = document.querySelector(`.step[data-step="${stepNum}"]`);
            const input = currentStepEl.querySelector('input[data-step], textarea[data-step]');
            
            // Валидация
            if (input && input.required) {
                if (!input.value || input.value.trim() === '') {
                    alert('Пожалуйста, заполните это поле');
                    input.focus();
                    return;
                }
                
                // Проверка телефона
                if (input.id === 'phone-input' && (input.value.includes('_') || input.value.length < 18)) {
                    alert('Пожалуйста, заполните номер телефона полностью');
                    input.focus();
                    return;
                }
            }
            
            // Скрыть текущий шаг
            currentStepEl.classList.remove('active');
            currentStepEl.style.opacity = '0.5';
            currentStepEl.style.pointerEvents = 'none';
            
            // Показать следующий
            const nextStep = stepNum + 1;
            const nextStepEl = document.querySelector(`.step[data-step="${nextStep}"]`);
            
            if (nextStepEl) {
                nextStepEl.classList.add('active');
                setTimeout(() => {
                    nextStepEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            }
        }
        
        // Отправка формы
        document.getElementById('warranty-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                user_id: window.SAAS_CONFIG.userId,
                form_id: window.SAAS_CONFIG.formId,
                fields: {}
            };
            
            // Собираем все поля в JSON
            for (let [key, value] of formData.entries()) {
                data.fields[key] = value;
            }
            
            try {
                const response = await fetch(window.SAAS_CONFIG.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('warranty-form').style.display = 'none';
                    document.querySelector('.end').style.display = 'block';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    alert(result.message || 'Ошибка при отправке');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ошибка при отправке формы');
            }
        });
    </script>
</body>
</html>
