# Warranty Activation Page / Страница активации гарантии

This is a warranty activation form page with **Apple Liquid Glass Design** inspired by https://i-laminat.ru/garantia/

## Files / Файлы

- **warranty.html** - Main HTML page with the warranty activation form
- **styles.css** - Complete styling with Apple liquid glass (glassmorphism) design
- **script.js** - Interactive functionality including:
  - Step-by-step navigation
  - Star rating system (5 stars shows Yandex review invitation)
  - Dynamic form fields
  - Multi-select for discounts
  - Form validation
  - Success message display

## Features / Функции

1. **Multi-step form** with 6 steps:
   - Step 1: Customer identification (phone or contract number)
   - Step 2: Additional work not in contract (with dynamic field addition)
   - Step 3: Sales staff rating (5-star system)
   - Step 4: Delivery rating (5-star system)
   - Step 5: Installation rating (5-star system)
   - Step 6: Discount reservations - **Multi-select checkboxes** for products

2. **Apple Liquid Glass Design**:
   - Frosted glass effect with backdrop blur
   - Gradient purple background
   - Glassmorphism UI elements
   - Smooth animations and transitions
   - Beautiful hover effects
   - Premium look and feel

3. **Interactive elements**:
   - Star rating with hover effects (text shows only on hover)
   - Yandex review invitation appears ONLY for 5-star ratings
   - Multi-select for discount products (Клей, Плинтус, Подложка, Грунтовка, Укладка)
   - "Nothing needed" option that deselects all products
   - Visual checkmark indicators on selected items
   - Dynamic textarea addition/removal
   - Form validation with error messages
   - Step completion indicators

4. **Responsive design** - Optimized for both desktop and mobile devices
   - Mobile: Stars displayed vertically with text always visible
   - Mobile: Product cards in 2 columns (1 column on very small screens)
   - Full-width buttons on mobile

## How to use / Как использовать

Simply open `warranty.html` in a web browser. All styles and scripts are self-contained and will work immediately.

## Design Features / Особенности дизайна

- **Glassmorphism**: Frosted glass effect with `backdrop-filter: blur(40px)`
- **Gradient Background**: Purple gradient (#667eea → #764ba2 → #f093fb)
- **Smooth animations**: All interactions use cubic-bezier easing
- **Checkmark indicators**: Visible on selected product cards
- **Premium shadows**: Multi-layer box shadows for depth
- **Shine effects**: Animated light sweep on button hover

## Customization / Настройка

- Colors can be changed in `styles.css` (primary: #BF081A, secondary: #2f6f30, background gradient)
- Glass blur intensity: adjust `backdrop-filter: blur()` values
- Form fields can be modified in `warranty.html`
- Behavior can be adjusted in `script.js`
