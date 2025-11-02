/**
 * Виджет для встраивания формы (Версия 2.0 - с form_id)
 * Использование: <script src="https://yourservice.com/widget.js?form=abc123xyz"></script>
 */

(function() {
    'use strict';
    
    // Получение form_id из параметра скрипта
    const scriptElement = document.currentScript || document.querySelector('script[src*="widget"]');
    const scriptSrc = scriptElement ? scriptElement.src : '';
    const urlParams = new URLSearchParams(scriptSrc.split('?')[1] || '');
    const formId = urlParams.get('form') || scriptElement?.getAttribute('data-form-id');
    
    if (!formId) {
        console.error('Warranty Widget: form ID not specified. Use ?form=YOUR_FORM_ID or data-form-id attribute');
        return;
    }
    
    // Определяем base URL
    const baseUrl = scriptSrc.split('/widget')[0] || window.location.origin;
    const formUrl = `${baseUrl}/form.php?id=${formId}`;
    
    // Конфигурация по умолчанию
    const defaultConfig = {
        position: 'bottom-right',
        buttonColor: '#c3202e',
        buttonText: '📋 Гарантия',
        openMode: 'modal' // 'modal' или 'newpage'
    };
    
    // Объединение с пользовательской конфигурацией
    const config = Object.assign({}, defaultConfig, window.warrantyWidgetConfig || {});
    
    // Создание кнопки
    const button = document.createElement('div');
    button.id = 'warranty-widget-button';
    button.textContent = config.buttonText;
    
    // Стили кнопки
    const buttonStyles = {
        position: 'fixed',
        zIndex: '9998',
        padding: '16px 24px',
        backgroundColor: config.buttonColor,
        color: 'white',
        border: 'none',
        borderRadius: '50px',
        fontWeight: '600',
        fontSize: '16px',
        cursor: 'pointer',
        boxShadow: '0 4px 20px rgba(0, 0, 0, 0.3)',
        transition: 'all 0.3s ease',
        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif'
    };
    
    // Позиционирование
    const positions = {
        'bottom-right': { bottom: '24px', right: '24px' },
        'bottom-left': { bottom: '24px', left: '24px' },
        'top-right': { top: '24px', right: '24px' },
        'top-left': { top: '24px', left: '24px' }
    };
    
    Object.assign(buttonStyles, positions[config.position] || positions['bottom-right']);
    Object.assign(button.style, buttonStyles);
    
    // Модальное окно
    let modal = null;
    
    function createModal() {
        modal = document.createElement('div');
        modal.id = 'warranty-widget-modal';
        
        const modalStyles = {
            position: 'fixed',
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            zIndex: '9999',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            opacity: '0',
            transition: 'opacity 0.3s ease'
        };
        
        Object.assign(modal.style, modalStyles);
        
        const iframe = document.createElement('iframe');
        iframe.src = formUrl;
        iframe.style.width = '90%';
        iframe.style.maxWidth = '800px';
        iframe.style.height = '90%';
        iframe.style.border = 'none';
        iframe.style.borderRadius = '16px';
        iframe.style.boxShadow = '0 20px 60px rgba(0, 0, 0, 0.5)';
        iframe.style.backgroundColor = 'white';
        
        const closeBtn = document.createElement('button');
        closeBtn.textContent = '✕';
        closeBtn.style.position = 'absolute';
        closeBtn.style.top = '20px';
        closeBtn.style.right = '20px';
        closeBtn.style.width = '40px';
        closeBtn.style.height = '40px';
        closeBtn.style.border = 'none';
        closeBtn.style.borderRadius = '50%';
        closeBtn.style.backgroundColor = 'white';
        closeBtn.style.color = '#000';
        closeBtn.style.fontSize = '24px';
        closeBtn.style.cursor = 'pointer';
        closeBtn.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.3)';
        closeBtn.style.transition = 'all 0.2s ease';
        
        closeBtn.onmouseover = () => {
            closeBtn.style.transform = 'scale(1.1)';
        };
        
        closeBtn.onmouseout = () => {
            closeBtn.style.transform = 'scale(1)';
        };
        
        closeBtn.onclick = closeModal;
        modal.onclick = (e) => {
            if (e.target === modal) closeModal();
        };
        
        modal.appendChild(iframe);
        modal.appendChild(closeBtn);
        document.body.appendChild(modal);
        
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
    }
    
    function closeModal() {
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(modal);
                modal = null;
            }, 300);
        }
    }
    
    // Обработчик клика
    button.onclick = () => {
        if (config.openMode === 'modal') {
            createModal();
        } else {
            window.open(formUrl, '_blank');
        }
    };
    
    // Hover эффект
    button.onmouseover = () => {
        button.style.transform = 'scale(1.05)';
        button.style.boxShadow = '0 6px 30px rgba(0, 0, 0, 0.4)';
    };
    
    button.onmouseout = () => {
        button.style.transform = 'scale(1)';
        button.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.3)';
    };
    
    // Добавление кнопки на страницу
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            document.body.appendChild(button);
        });
    } else {
        document.body.appendChild(button);
    }
    
    // API для программного управления
    window.WarrantyWidget = {
        open: () => button.onclick(),
        close: closeModal,
        show: () => { button.style.display = 'block'; },
        hide: () => { button.style.display = 'none'; },
        getFormId: () => formId,
        getFormUrl: () => formUrl
    };
    
    console.log(`Warranty Widget loaded. Form ID: ${formId}`);
    
})();
