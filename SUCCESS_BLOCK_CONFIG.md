# Настройка блока успешной отправки формы

Этот документ описывает, как настроить блок, который показывается после успешной активации гарантии.

## Расположение кода

Блок находится в файле `warranty.html`, в самом конце формы (строка ~357).

## Структура блока

```html
<div class="success-block" style="display: none;">
    <div class="success-header">
        <!-- Иконка/картинка слева -->
        <div class="success-icon">
            <img src="images/success-icon.png" alt="Успех">
        </div>
        
        <!-- Текст справа -->
        <div class="success-text">
            <h1>Заголовок</h1>
            <p>Подзаголовок</p>
        </div>
    </div>

    <!-- Три карточки -->
    <div class="success-cards">
        <a href="/ссылка1" class="success-card">...</a>
        <a href="/ссылка2" class="success-card">...</a>
        <a href="/ссылка3" class="success-card">...</a>
    </div>
</div>
```

---

## 1. Изменение основной иконки/картинки

### Вариант А: Использовать свою картинку

Замените путь к изображению:
```html
<img src="images/success-icon.png" alt="Успех" class="success-image">
```

На:
```html
<img src="images/ваша-картинка.png" alt="Успех" class="success-image">
```

**Рекомендуемый размер:** 120x120px, PNG с прозрачным фоном

### Вариант Б: Использовать иконку галочки (по умолчанию)

Удалите тег `<img>`, останется только:
```html
<div class="success-icon">
    <!-- Автоматически появится зеленая галочка ✓ -->
</div>
```

---

## 2. Изменение текста

### Заголовок
```html
<h1 class="success-title">Ваш новый заголовок</h1>
```

### Подзаголовок
```html
<p class="success-subtitle">Ваш новый текст подзаголовка</p>
```

---

## 3. Настройка карточек

Каждая карточка имеет такую структуру:

```html
<a href="/ссылка" class="success-card" target="_blank">
    <!-- Картинка карточки -->
    <div class="success-card-image" 
         style="background-image: url('images/картинка.jpg');"></div>
    
    <!-- Содержимое -->
    <div class="success-card-content">
        <h3 class="success-card-title">Заголовок карточки</h3>
        <p class="success-card-description">Описание карточки</p>
    </div>
    
    <!-- Стрелка (автоматическая) -->
    <div class="success-card-arrow">→</div>
</a>
```

### 3.1. Карточка 1 - Новинки

**Изменить ссылку:**
```html
<a href="/novинки" class="success-card" target="_blank">
```
Замените `/novинки` на нужный URL

**Изменить картинку:**
```html
style="background-image: url('images/card-new.jpg');"
```
Замените `card-new.jpg` на вашу картинку

**Рекомендуемый размер:** 400x300px

**Изменить текст:**
```html
<h3 class="success-card-title">Новинки</h3>
<p class="success-card-description">Ознакомьтесь с нашими последними поступлениями</p>
```

### 3.2. Карточка 2 - Полезные статьи

**Изменить ссылку:**
```html
<a href="/articles" class="success-card" target="_blank">
```

**Изменить картинку:**
```html
style="background-image: url('images/card-articles.jpg');"
```

**Изменить текст:**
```html
<h3 class="success-card-title">Полезные статьи</h3>
<p class="success-card-description">Советы по уходу и эксплуатации покрытий</p>
```

### 3.3. Карточка 3 - Условия гарантии

**Изменить ссылку:**
```html
<a href="/garantiya" class="success-card" target="_blank">
```

**Изменить картинку:**
```html
style="background-image: url('images/card-warranty.jpg');"
```

**Изменить текст:**
```html
<h3 class="success-card-title">Условия гарантии</h3>
<p class="success-card-description">Подробная информация о гарантийных условиях</p>
```

---

## 4. Добавление/удаление карточек

### Добавить четвертую карточку

Скопируйте любую существующую карточку и вставьте после последней:

```html
<a href="/новая-ссылка" class="success-card" target="_blank">
    <div class="success-card-image" style="background-image: url('images/новая-картинка.jpg');"></div>
    <div class="success-card-content">
        <h3 class="success-card-title">Новая карточка</h3>
        <p class="success-card-description">Описание новой карточки</p>
    </div>
    <div class="success-card-arrow">→</div>
</a>
```

**Важно:** Если карточек больше 3, они автоматически перестроятся в сетку 2x2 на планшетах и в столбик на телефонах.

### Удалить карточку

Просто удалите весь блок `<a href="..." class="success-card">...</a>` нужной карточки.

---

## 5. Изменение цветов

Цвета настраиваются в файле `styles.css`, секция `.success-block`:

### Цвет заголовка
```css
.success-title {
    color: #BF081A; /* Замените на свой цвет */
}
```

### Цвет круглой иконки
```css
.success-icon {
    background: linear-gradient(135deg, #c3202e 0%, #e02d3c 100%);
}
```

### Цвет кнопки-стрелки
```css
.success-card-arrow {
    background: rgba(195, 32, 46, 0.9);
}
```

---

## 6. Открытие ссылок

### Открывать в новой вкладке
```html
<a href="/ссылка" class="success-card" target="_blank">
```

### Открывать в той же вкладке
```html
<a href="/ссылка" class="success-card">
```
Просто удалите `target="_blank"`

---

## 7. Примеры готовых конфигураций

### Пример 1: Простой вариант без картинок

```html
<div class="success-header">
    <div class="success-icon">
        <!-- Автоматическая галочка -->
    </div>
    <div class="success-text">
        <h1 class="success-title">Готово!</h1>
        <p class="success-subtitle">Ваша гарантия активирована</p>
    </div>
</div>
```

### Пример 2: С внешними ссылками

```html
<a href="https://example.com/catalog" class="success-card" target="_blank">
    <div class="success-card-image" style="background-image: url('https://example.com/image.jpg');"></div>
    <div class="success-card-content">
        <h3 class="success-card-title">Каталог</h3>
        <p class="success-card-description">Посмотреть весь каталог</p>
    </div>
    <div class="success-card-arrow">→</div>
</a>
```

---

## 8. Требования к изображениям

### Основная иконка (круглая)
- **Формат:** PNG с прозрачным фоном
- **Размер:** 120x120px или больше
- **Пропорции:** Квадрат 1:1

### Картинки карточек
- **Формат:** JPG или PNG
- **Размер:** 400x300px или больше (пропорция 4:3)
- **Вес:** Рекомендуется до 200KB для быстрой загрузки

### Где разместить изображения

Создайте папку `images` в корне проекта и поместите туда все изображения:
```
/workspace/
  - images/
    - success-icon.png
    - card-new.jpg
    - card-articles.jpg
    - card-warranty.jpg
```

---

## 9. Тестирование

1. Заполните форму на сайте
2. Нажмите "Активировать гарантию"
3. Проверьте, что блок отображается корректно
4. Проверьте все ссылки - они должны работать
5. Проверьте на мобильном устройстве

---

## 10. Адаптивность

Блок автоматически адаптируется под разные экраны:

- **Десктоп (>1024px):** 3 карточки в ряд
- **Планшет (768-1024px):** 2 карточки в ряд, третья на новой строке
- **Мобильный (<768px):** 1 карточка в столбик

---

## Нужна помощь?

Если что-то не получается:
1. Проверьте пути к изображениям
2. Убедитесь, что не удалили важные классы CSS
3. Проверьте, что ссылки начинаются с `/` или `http://` / `https://`

Все настройки находятся прямо в HTML и легко меняются без знания программирования! 🎨
