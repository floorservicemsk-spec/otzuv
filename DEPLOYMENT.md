# Инструкция по развертыванию

## Требования

- Node.js 18+ 
- npm или yarn

## Установка

```bash
npm install
```

## Переменные окружения

Создайте файл `.env.local` для настройки:

```env
# Аналитика
NEXT_PUBLIC_GA_ID=your-google-analytics-id
NEXT_PUBLIC_YANDEX_METRICA_ID=your-yandex-metrica-id

# API ключи (если нужны)
CRM_API_KEY=your-crm-api-key
SMS_API_KEY=your-sms-api-key
EMAIL_API_KEY=your-email-api-key

# reCAPTCHA (если используется)
NEXT_PUBLIC_RECAPTCHA_SITE_KEY=your-recaptcha-site-key
RECAPTCHA_SECRET_KEY=your-recaptcha-secret-key
```

## Запуск в разработке

```bash
npm run dev
```

Откройте [http://localhost:3000/garantia](http://localhost:3000/garantia)

## Сборка для продакшена

```bash
npm run build
npm start
```

## Тестирование

### Тестовые данные

**Телефон:**
```
+7 (999) 999-99-99
```

**Договор с монтажом:**
```
IL-123456
```

**Договор без монтажа:**
```
IL-234567
```

**Договор с префиксом D-:**
```
D-123456
```

### Проверка функциональности

1. ✅ Идентификация обязательна
2. ✅ Валидация телефона и договора
3. ✅ Пропуск шага монтажников для договоров без монтажа
4. ✅ Выбор скидок с логикой "Ничего не нужно"
5. ✅ Сохранение драфта в localStorage
6. ✅ Обработка ошибок сети
7. ✅ Rate limiting (5 запросов/час)
8. ✅ Аналитика событий

## Интеграции

### Google Analytics 4

Добавьте в `app/layout.tsx`:

```tsx
<Script
  src={`https://www.googletagmanager.com/gtag/js?id=${process.env.NEXT_PUBLIC_GA_ID}`}
  strategy="afterInteractive"
/>
<Script id="google-analytics" strategy="afterInteractive">
  {`
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '${process.env.NEXT_PUBLIC_GA_ID}');
  `}
</Script>
```

### Яндекс.Метрика

Добавьте в `app/layout.tsx`:

```tsx
<Script id="yandex-metrica" strategy="afterInteractive">
  {`
    (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
    m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
    (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
    ym(${process.env.NEXT_PUBLIC_YANDEX_METRICA_ID}, "init", {
      clickmap:true,
      trackLinks:true,
      accurateTrackBounce:true
    });
  `}
</Script>
```

### reCAPTCHA v3

Установите пакет:
```bash
npm install react-google-recaptcha-v3
```

Добавьте компонент в форму перед отправкой.

## Структура БД (пример)

Для полноценной работы рекомендуется создать таблицы:

```sql
CREATE TABLE contracts (
  id SERIAL PRIMARY KEY,
  phone VARCHAR(20),
  contract_number VARCHAR(50) UNIQUE,
  has_installation BOOLEAN DEFAULT FALSE,
  status VARCHAR(20),
  created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE warranties (
  id SERIAL PRIMARY KEY,
  warranty_id VARCHAR(50) UNIQUE NOT NULL,
  contract_id VARCHAR(50),
  phone_or_contract VARCHAR(100),
  activated_at TIMESTAMP DEFAULT NOW(),
  activated_by_ip VARCHAR(45),
  channel VARCHAR(50),
  FOREIGN KEY (contract_id) REFERENCES contracts(contract_number)
);

CREATE TABLE warranty_feedback (
  id SERIAL PRIMARY KEY,
  warranty_id VARCHAR(50),
  sales_rate INTEGER CHECK (sales_rate BETWEEN 1 AND 5),
  delivery_rate INTEGER CHECK (delivery_rate BETWEEN 1 AND 5),
  installation_rate INTEGER CHECK (installation_rate BETWEEN 1 AND 5),
  comment TEXT,
  FOREIGN KEY (warranty_id) REFERENCES warranties(warranty_id)
);

CREATE TABLE extra_work (
  id SERIAL PRIMARY KEY,
  warranty_id VARCHAR(50),
  title VARCHAR(255) NOT NULL,
  price DECIMAL(10,2),
  FOREIGN KEY (warranty_id) REFERENCES warranties(warranty_id)
);

CREATE TABLE reserved_discounts (
  id SERIAL PRIMARY KEY,
  warranty_id VARCHAR(50),
  code VARCHAR(50) NOT NULL,
  percent INTEGER,
  reserved_until DATE,
  FOREIGN KEY (warranty_id) REFERENCES warranties(warranty_id)
);
```

## Поддержка

По вопросам обращайтесь к команде разработки.
