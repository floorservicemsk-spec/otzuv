# Настройка изображений / Image Setup

## Логотип / Logo

Разместите ваш логотип в корневой папке проекта с именем `logo.png`

Или измените путь в `warranty.html`:
```html
<img src="logo.png" alt="Логотип" class="logo-image">
```

Рекомендуемый размер: 200x80px (или соотношение 2.5:1)

## Изображения товаров / Product Images

Создайте папку `images` и разместите в ней изображения товаров:

### Требуемые файлы:
- `images/glue.jpg` - Клей
- `images/baseboard.jpg` - Плинтус
- `images/underlay.jpg` - Подложка
- `images/primer.jpg` - Грунтовка
- `images/installation.jpg` - Укладка

### Рекомендации:
- Размер: 300x300px (квадратные)
- Формат: JPG или PNG
- Качество: Хорошее, но оптимизированное для веб

## Если изображений нет

Временно используются цветные плейсхолдеры. Чтобы добавить их:

```css
/* В styles.css добавьте для каждого товара: */
.big-img[style*="glue.jpg"] {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

## Структура папок / Folder Structure

```
/workspace/
  ├── warranty.html
  ├── styles.css
  ├── script.js
  ├── send-warranty.php
  ├── logo.png          ← ВАШ ЛОГОТИП
  └── images/
      ├── glue.jpg
      ├── baseboard.jpg
      ├── underlay.jpg
      ├── primer.jpg
      └── installation.jpg
```
