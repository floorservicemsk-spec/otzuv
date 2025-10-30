# API Documentation

## Base URL

```
Development: http://localhost:3000/api
Production: https://i-laminat.ru/api
```

## Authentication

Currently no authentication is required for the warranty activation endpoint. In production, consider adding:
- API key authentication
- CORS restrictions
- Rate limiting per IP/session

## Endpoints

### 1. Activate Warranty

Activates a warranty and reserves discounts for the customer.

**Endpoint:** `POST /warranty/activate`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "phone_or_contract": "IL-123456",
  "has_extra_work": true,
  "extra_work": [
    {
      "title": "Установка порога",
      "price": 1500
    },
    {
      "title": "Обработка углов",
      "price": 800
    }
  ],
  "sales_rate": 5,
  "delivery_rate": 4,
  "installation_rate": 5,
  "discounts": ["glue_10", "installation_30"]
}
```

**Field Descriptions:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| phone_or_contract | string | Yes | Phone number (+7 format) or contract ID (IL-, D-, or numeric) |
| has_extra_work | boolean | No | Whether additional work was performed |
| extra_work | array | No | List of additional work items |
| extra_work[].title | string | Yes (if extra_work) | Description of the work |
| extra_work[].price | number | No | Cost of the work |
| sales_rate | integer (1-5) | No | Rating for sales service |
| delivery_rate | integer (1-5) | No | Rating for delivery service |
| installation_rate | integer (1-5) | No | Rating for installation service |
| discounts | array | No | List of discount codes to reserve |

**Validation Rules:**

- `phone_or_contract`:
  - Phone: Must match format +7 (XXX) XXX-XX-XX
  - Contract: Prefix IL-, D- or numeric, length 6-16 characters
  
- `extra_work[].title`:
  - Required if extra_work array is not empty
  - Minimum length: 1 character
  
- Rating fields (sales_rate, delivery_rate, installation_rate):
  - Must be integer between 1 and 5
  - Optional
  
- `discounts`:
  - Valid codes: glue_10, molding_5, underlay_5, primer_10, installation_30, none
  - Multiple selection allowed
  - If "none" is selected, other discounts are ignored

**Success Response (200):**
```json
{
  "activated": true,
  "warranty_id": "W-2025-000001",
  "contract_id": "IL-123456",
  "discounts_reserved_until": "2025-11-13T12:00:00.000Z"
}
```

**Error Responses:**

**400 Bad Request** - Invalid data
```json
{
  "error": "Некорректные данные формы",
  "details": [
    {
      "path": ["phone_or_contract"],
      "message": "Введите корректный телефон или номер договора"
    }
  ]
}
```

**404 Not Found** - Contract/phone not found
```json
{
  "error": "Договор или телефон не найден. Проверьте введённые данные."
}
```

**409 Conflict** - Already activated
```json
{
  "error": "Гарантия для этого договора уже активирована",
  "already_activated": true
}
```

**429 Too Many Requests** - Rate limit exceeded
```json
{
  "error": "Превышен лимит запросов. Попробуйте позже."
}
```

**500 Internal Server Error**
```json
{
  "error": "Внутренняя ошибка сервера. Попробуйте позже."
}
```

---

### 2. Check Warranty Status

Check if a warranty has been activated for a contract.

**Endpoint:** `GET /warranty/status`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| contract | string | Yes | Contract ID (e.g., IL-123456) |

**Example:**
```
GET /warranty/status?contract=IL-123456
```

**Success Response (200) - Activated:**
```json
{
  "activated": true,
  "warranty_id": "W-2025-000001",
  "contract_id": "IL-123456",
  "activated_at": "2025-10-30T12:00:00.000Z",
  "discounts_reserved_until": "2025-11-13T12:00:00.000Z"
}
```

**Success Response (200) - Not Activated:**
```json
{
  "activated": false
}
```

**Error Response (400):**
```json
{
  "error": "Параметр contract обязателен"
}
```

**Error Response (500):**
```json
{
  "error": "Внутренняя ошибка сервера"
}
```

---

## Rate Limiting

**Current Limits:**
- 5 requests per hour per IP address
- Additional limits may be applied per contract/phone number

**Rate Limit Headers:**
```
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 4
X-RateLimit-Reset: 1698676800
```

When rate limit is exceeded, the API returns a 429 status code with retry information.

---

## Error Handling

All errors follow a consistent format:

```json
{
  "error": "Human-readable error message in Russian",
  "details": {} // Optional additional error details
}
```

**HTTP Status Codes:**
- `200` - Success
- `400` - Bad Request (validation errors)
- `404` - Not Found (contract/phone not found)
- `409` - Conflict (warranty already activated)
- `422` - Unprocessable Entity (data conflicts)
- `429` - Too Many Requests (rate limit exceeded)
- `500` - Internal Server Error

---

## Examples

### Example 1: Minimal Activation (Only Required Fields)

**Request:**
```bash
curl -X POST http://localhost:3000/api/warranty/activate \
  -H "Content-Type: application/json" \
  -d '{
    "phone_or_contract": "+7 (999) 123-45-67",
    "has_extra_work": false,
    "extra_work": [],
    "discounts": []
  }'
```

**Response:**
```json
{
  "activated": true,
  "warranty_id": "W-2025-000001",
  "contract_id": "IL-123456",
  "discounts_reserved_until": "2025-11-13T12:00:00.000Z"
}
```

---

### Example 2: Full Activation with All Features

**Request:**
```bash
curl -X POST http://localhost:3000/api/warranty/activate \
  -H "Content-Type: application/json" \
  -d '{
    "phone_or_contract": "IL-123456",
    "has_extra_work": true,
    "extra_work": [
      {
        "title": "Установка порога",
        "price": 1500
      }
    ],
    "sales_rate": 5,
    "delivery_rate": 5,
    "installation_rate": 4,
    "discounts": ["glue_10", "installation_30"]
  }'
```

**Response:**
```json
{
  "activated": true,
  "warranty_id": "W-2025-000002",
  "contract_id": "IL-123456",
  "discounts_reserved_until": "2025-11-13T12:00:00.000Z"
}
```

---

### Example 3: Check Status

**Request:**
```bash
curl -X GET "http://localhost:3000/api/warranty/status?contract=IL-123456"
```

**Response:**
```json
{
  "activated": true,
  "warranty_id": "W-2025-000001",
  "contract_id": "IL-123456",
  "activated_at": "2025-10-30T12:00:00.000Z",
  "discounts_reserved_until": "2025-11-13T12:00:00.000Z"
}
```

---

## Webhooks (Future)

In production, consider implementing webhooks to notify external systems:

- `warranty.activated` - When a warranty is activated
- `warranty.feedback.received` - When customer feedback is submitted
- `discount.reserved` - When discounts are reserved
- `discount.used` - When reserved discounts are used

---

## Integration Guide

### Step 1: Collect Form Data
Use the provided React components or create your own form to collect:
- Phone or contract ID
- Optional: Extra work details
- Optional: Service ratings (1-5)
- Optional: Discount selections

### Step 2: Validate Client-Side
Use the Zod schema provided in `lib/validation.ts` for client-side validation.

### Step 3: Submit to API
POST the data to `/api/warranty/activate` endpoint.

### Step 4: Handle Response
- **Success (200)**: Show success page with warranty ID
- **Error (4xx/5xx)**: Display error message to user

### Step 5: Track Analytics
Fire appropriate analytics events using the `lib/analytics.ts` helper.

---

## Security Considerations

1. **Rate Limiting**: Implement proper rate limiting in production (Redis-based)
2. **CAPTCHA**: Add reCAPTCHA or Cloudflare Turnstile to prevent bots
3. **Input Sanitization**: All inputs are sanitized server-side
4. **SQL Injection**: Use parameterized queries (prepared statements)
5. **XSS Protection**: React escapes outputs by default
6. **CORS**: Configure proper CORS headers for production domain
7. **HTTPS**: Always use HTTPS in production
8. **API Keys**: Consider adding API key authentication for production

---

## Performance

**Expected Response Times:**
- Warranty Activation: < 500ms
- Status Check: < 100ms

**Optimization Tips:**
- Use Redis for rate limiting (faster than database)
- Cache discount catalog
- Use connection pooling for database
- Implement CDN for static assets
- Enable gzip compression

---

## Support

For API support or issues:
- Email: api@i-laminat.ru
- Technical Support: 8 (800) 123-45-67
- Documentation: https://i-laminat.ru/docs/api
