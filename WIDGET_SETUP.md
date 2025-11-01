# Инструкция по установке виджета активации гарантии

Виджет позволяет встроить кнопку активации гарантии на любую страницу вашего сайта.

## 📦 Способы установки

### Способ 1: Простая установка (Рекомендуется)

Добавьте эту строку перед закрывающим тегом `</body>` на всех страницах вашего сайта:

```html
<script src="/warranty-widget.js"></script>
```

**Готово!** Виджет автоматически появится на всех страницах.

---

### Способ 2: Установка через CDN (если виджет на другом домене)

Если форма гарантии находится на другом домене, используйте полный URL:

```html
<script src="https://ваш-сайт.ru/warranty-widget.js"></script>
```

И измените `formUrl` в файле `warranty-widget.js`:

```javascript
formUrl: 'https://ваш-сайт.ru/warranty.html',
```

---

### Способ 3: Встраивание через Google Tag Manager

1. Зайдите в Google Tag Manager
2. Создайте новый тег → **Custom HTML**
3. Вставьте код:

```html
<script src="/warranty-widget.js"></script>
```

4. Триггер: **All Pages** (все страницы)
5. Опубликуйте изменения

---

## ⚙️ Настройка виджета

Откройте файл `warranty-widget.js` и измените параметры в начале файла:

### Основные настройки

```javascript
const config = {
    // URL страницы с формой гарантии
    formUrl: '/warranty.html',
    
    // Позиция кнопки: 'bottom-right', 'bottom-left', 'top-right', 'top-left'
    position: 'bottom-right',
    
    // Отступ от края экрана (в пикселях)
    offset: 20,
    
    // Цвет кнопки
    buttonColor: '#c3202e',
    
    // Текст на кнопке
    buttonText: 'Активировать гарантию',
    
    // Показывать как: 'modal' (модальное окно) или 'newpage' (новая страница)
    displayMode: 'modal',
    
    // Автоматически показывать кнопку через N секунд (0 = сразу)
    showDelay: 0
};
```

---

## 🎨 Примеры настройки

### Пример 1: Кнопка в левом нижнем углу

```javascript
position: 'bottom-left',
buttonColor: '#1e40af',
buttonText: 'Гарантия'
```

### Пример 2: Открытие в новой вкладке вместо модального окна

```javascript
displayMode: 'newpage'
```

### Пример 3: Показать кнопку через 3 секунды после загрузки

```javascript
showDelay: 3000
```

### Пример 4: Изменить цвет на зелёный

```javascript
buttonColor: '#10b981'
```

---

## 📱 Адаптивность

Виджет автоматически адаптируется под мобильные устройства:
- На мобильных: текст скрывается, остаётся только иконка
- Кнопка всегда в правом нижнем углу на телефонах
- Модальное окно занимает весь экран

---

## 🎯 Расширенное использование

### Открыть виджет программно из кода

```javascript
// Открыть форму
WarrantyWidget.open();

// Закрыть форму (если открыта в модальном окне)
WarrantyWidget.close();

// Изменить настройки на лету
WarrantyWidget.updateConfig({
    buttonColor: '#ff0000',
    buttonText: 'Новый текст'
});
```

### Открыть виджет при клике на свой элемент

```html
<button onclick="WarrantyWidget.open()">Активировать гарантию</button>
```

или

```javascript
document.getElementById('myButton').addEventListener('click', function() {
    WarrantyWidget.open();
});
```

---

## 🚫 Исключение страниц

Если нужно НЕ показывать виджет на определённых страницах, добавьте перед подключением скрипта:

```html
<script>
    // Не показывать виджет на странице контактов
    if (window.location.pathname === '/contacts') {
        window.warrantyWidgetLoaded = true;
    }
</script>
<script src="/warranty-widget.js"></script>
```

Или для нескольких страниц:

```html
<script>
    const excludePages = ['/contacts', '/about', '/warranty.html'];
    if (excludePages.includes(window.location.pathname)) {
        window.warrantyWidgetLoaded = true;
    }
</script>
<script src="/warranty-widget.js"></script>
```

---

## 🎨 Изменение внешнего вида

### Изменить иконку на кнопке

Найдите в `warranty-widget.js` строку с SVG и замените на свою иконку:

```javascript
button.innerHTML = `
    <svg>...</svg> <!-- Ваша SVG иконка -->
    <span>${config.buttonText}</span>
`;
```

### Изменить форму кнопки (например, на квадратную)

В `warranty-widget.js` найдите `.warranty-widget-button` и измените:

```css
border-radius: 12px; /* вместо 50px */
```

### Добавить пульсирующую анимацию

В `warranty-widget.js` добавьте в стили:

```css
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.warranty-widget-button {
    animation: pulse 2s infinite;
}
```

---

## 🔧 Устранение проблем

### Кнопка не появляется

1. Проверьте, что скрипт загружен (откройте консоль браузера F12)
2. Проверьте путь к файлу `warranty-widget.js`
3. Убедитесь, что на странице нет другого элемента с `z-index` выше 999999

### Модальное окно не открывается

1. Проверьте путь к `formUrl` в настройках
2. Убедитесь, что файл `warranty.html` доступен
3. Проверьте консоль браузера на наличие ошибок CORS (если форма на другом домене)

### Кнопка закрывает важный элемент

Измените `position` и `offset` в настройках:

```javascript
position: 'bottom-left', // или другая позиция
offset: 100 // больший отступ
```

---

## 📊 Статистика кликов (опционально)

Для отслеживания кликов добавьте в `warranty-widget.js` после `button.addEventListener('click', openWarrantyForm);`:

```javascript
button.addEventListener('click', function() {
    // Google Analytics 4
    if (typeof gtag !== 'undefined') {
        gtag('event', 'warranty_button_click', {
            'event_category': 'engagement',
            'event_label': 'warranty_widget'
        });
    }
    
    // Яндекс.Метрика
    if (typeof ym !== 'undefined') {
        ym(XXXXXXXX, 'reachGoal', 'warranty_click'); // Замените XXXXXXXX на ваш ID
    }
    
    openWarrantyForm();
});
```

---

## 🌐 Многоязычность

Для разных языков создайте конфиги:

```javascript
const language = document.documentElement.lang || 'ru';

const translations = {
    ru: {
        buttonText: 'Активировать гарантию',
        formUrl: '/warranty.html'
    },
    en: {
        buttonText: 'Activate Warranty',
        formUrl: '/warranty-en.html'
    }
};

const config = {
    ...translations[language],
    position: 'bottom-right',
    // остальные настройки
};
```

---

## ✅ Проверка установки

После установки откройте любую страницу сайта и проверьте:

1. ✅ Кнопка виджета видна в указанном углу
2. ✅ При клике открывается форма (в модальном окне или новой вкладке)
3. ✅ Кнопка закрытия работает (для модального окна)
4. ✅ На мобильном отображается корректно

---

## 📞 Примеры интеграции

### Добавить в WordPress

В файл `footer.php` вашей темы перед `</body>`:

```php
<script src="<?php echo get_template_directory_uri(); ?>/warranty-widget.js"></script>
```

### Добавить в Tilda

1. Настройки сайта → Еще → HTML-код для вставки внутрь HEAD
2. Или в футере страницы через блок T123 "HTML-код"

### Добавить в Bitrix

В шаблон сайта в файл `footer.php`:

```php
<script src="<?=SITE_TEMPLATE_PATH?>/warranty-widget.js"></script>
```

---

## 🎉 Готово!

Виджет установлен и готов к работе. При возникновении вопросов смотрите раздел "Устранение проблем" выше.

**Важно:** Не забудьте протестировать виджет на разных устройствах и браузерах перед запуском в продакшен!
