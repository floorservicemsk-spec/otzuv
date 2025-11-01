/**
 * JavaScript для формы активации гарантии
 * Адаптировано из оригинального script.js
 */

document.addEventListener('DOMContentLoaded', function() {
    initPhoneMask();
    initStarRatings();
    initSubmitButtonControl();
    initFormSubmission();
});

// Маска для телефона
function initPhoneMask() {
    const phoneInput = document.getElementById('phone-input');
    if (phoneInput) {
        const mask = IMask(phoneInput, {
            mask: '+{7} (000) 000-00-00'
        });
        
        // Валидация на blur
        phoneInput.addEventListener('blur', function() {
            if (this.value.includes('_') || this.value.length < 18) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
        
        phoneInput.addEventListener('input', function() {
            if (!this.value.includes('_') && this.value.length >= 18) {
                this.classList.remove('error');
                markStepCompleted(1);
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

// Управление кнопкой отправки
function initSubmitButtonControl() {
    const consentCheckbox = document.getElementById('consent');
    const submitBtn = document.getElementById('submit-btn');
    
    if (consentCheckbox && submitBtn) {
        consentCheckbox.addEventListener('change', function() {
            submitBtn.disabled = !this.checked;
        });
    }
}

// Отправка формы
function initFormSubmission() {
    const form = document.getElementById('warranty-form');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Валидация
        const phoneInput = document.getElementById('phone-input');
        if (phoneInput.value.includes('_') || phoneInput.value.length < 18) {
            alert('Пожалуйста, заполните номер телефона полностью');
            phoneInput.classList.add('error');
            return;
        }
        
        // Сбор данных
        const formData = new FormData(form);
        formData.append('user_id', window.SAAS_CONFIG.userId);
        
        // Отправка
        try {
            const response = await fetch(window.SAAS_CONFIG.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showSuccessMessage();
            } else {
                alert(result.message || 'Ошибка при отправке формы');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка при отправке формы');
        }
    });
}

// Отправка рейтинга
function submitRating(group) {
    const container = document.querySelector(`[data-rating-group="${group}"]`);
    const hiddenInput = container.parentElement.querySelector('input[type="hidden"]');
    
    if (!hiddenInput.value) {
        alert('Пожалуйста, выберите оценку');
        return;
    }
    
    const step = parseInt(hiddenInput.getAttribute('data-step'));
    markStepCompleted(step);
}

// Пометка шага как завершенного
function markStepCompleted(stepNumber) {
    const currentStep = document.querySelector(`.step[data-step="${stepNumber}"]`);
    const nextStep = document.querySelector(`.step[data-step="${stepNumber + 1}"]`);
    
    if (currentStep) {
        currentStep.style.opacity = '0.5';
        currentStep.style.pointerEvents = 'none';
    }
    
    if (nextStep) {
        nextStep.style.display = 'block';
        setTimeout(() => {
            nextStep.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
}

// Показ сообщения об успехе
function showSuccessMessage() {
    const form = document.getElementById('warranty-form');
    const successMessage = document.querySelector('.end');
    
    form.style.display = 'none';
    successMessage.style.display = 'block';
    
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
