import { NextRequest, NextResponse } from 'next/server'
import { z } from 'zod'
import { warrantyFormSchema } from '@/lib/schemas'

// Rate limiting (простая реализация в памяти, в продакшене использовать Redis)
const rateLimitMap = new Map<string, { count: number; resetAt: number }>()

function checkRateLimit(ip: string): boolean {
  const now = Date.now()
  const limit = rateLimitMap.get(ip)

  if (!limit || now > limit.resetAt) {
    rateLimitMap.set(ip, { count: 1, resetAt: now + 60 * 60 * 1000 }) // 1 час
    return true
  }

  if (limit.count >= 5) {
    return false
  }

  limit.count++
  return true
}

function getClientIP(request: NextRequest): string {
  const forwarded = request.headers.get('x-forwarded-for')
  if (forwarded) {
    return forwarded.split(',')[0].trim()
  }
  return request.headers.get('x-real-ip') || 'unknown'
}

// Схема для API запроса
const activateRequestSchema = z.object({
  phone_or_contract: z.string().min(1),
  has_extra_work: z.boolean(),
  extra_work: z.array(
    z.object({
      title: z.string(),
      price: z.number().nullable().optional(),
    })
  ),
  rates: z.object({
    sales: z.number().int().min(1).max(5).nullable().optional(),
    delivery: z.number().int().min(1).max(5).nullable().optional(),
    installation: z.number().int().min(1).max(5).nullable().optional(),
  }),
  discounts: z.array(z.string()),
})

// Симуляция проверки договора/телефона
async function validateContractOrPhone(
  value: string
): Promise<{ valid: boolean; contractId?: string; hasInstallation?: boolean }> {
  // TODO: Реальная интеграция с БД/CRM
  // Здесь простая проверка формата
  
  const contractRegex = /^(IL-|D-)?\d{6,16}$/i
  const normalized = value.replace(/\s/g, '')
  
  if (contractRegex.test(normalized)) {
    // Симуляция: если договор начинается с IL-1, то есть монтаж
    return {
      valid: true,
      contractId: normalized.toUpperCase(),
      hasInstallation: normalized.toUpperCase().startsWith('IL-1'),
    }
  }

  // Проверка телефона
  const phoneRegex = /^\+7\d{10}$/
  const phoneDigits = value.replace(/\D/g, '')
  if (phoneDigits.length === 11 && phoneDigits.startsWith('7')) {
    return {
      valid: true,
      contractId: undefined,
      hasInstallation: false, // По умолчанию для телефона
    }
  }

  return { valid: false }
}

// Симуляция проверки, была ли уже активирована гарантия
async function checkAlreadyActivated(
  contractId: string
): Promise<boolean> {
  // TODO: Проверка в БД
  return false
}

// Генерация ID гарантийного талона
function generateWarrantyId(): string {
  const year = new Date().getFullYear()
  const random = Math.floor(Math.random() * 1000000)
  return `W-${year}-${random.toString().padStart(6, '0')}`
}

// Симуляция сохранения в CRM/БД
async function saveToCRM(data: any) {
  // TODO: Реальная интеграция с CRM
  console.log('Saving to CRM:', data)
  return true
}

// Отправка уведомления в Telegram
async function sendTelegramNotification(data: {
  warrantyId: string
  phoneOrContract: string
  hasExtraWork: boolean
  extraWork: any[]
  rates: any
  discounts: string[]
  discountsReservedUntil: string
}) {
  const botToken = process.env.TELEGRAM_BOT_TOKEN
  const chatId = process.env.TELEGRAM_CHAT_ID

  if (!botToken || !chatId) {
    console.warn('Telegram credentials not configured')
    return false
  }

  try {
    // Форматирование сообщения
    const message = `
🎉 <b>Новая активация гарантии!</b>

📋 <b>Гарантийный талон:</b> ${data.warrantyId}
📱 <b>Телефон/Договор:</b> ${data.phoneOrContract}

⭐️ <b>Оценки:</b>
${data.rates.sales ? `• Продавцы: ${'⭐'.repeat(data.rates.sales)}` : ''}
${data.rates.delivery ? `• Доставка: ${'⭐'.repeat(data.rates.delivery)}` : ''}
${data.rates.installation ? `• Монтажники: ${'⭐'.repeat(data.rates.installation)}` : ''}

${data.hasExtraWork && data.extraWork.length > 0 ? `
🔧 <b>Дополнительные работы:</b>
${data.extraWork.map(work => `• ${work.title}${work.price ? ` - ${work.price} ₽` : ''}`).join('\n')}
` : ''}

${data.discounts.length > 0 && !data.discounts.includes('none') ? `
🎁 <b>Забронированные скидки:</b>
${data.discounts.map(code => `• ${code}`).join('\n')}
📅 Действительны до: ${data.discountsReservedUntil}
` : ''}

⏰ <b>Дата активации:</b> ${new Date().toLocaleString('ru-RU')}
    `.trim()

    const response = await fetch(
      `https://api.telegram.org/bot${botToken}/sendMessage`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          chat_id: chatId,
          text: message,
          parse_mode: 'HTML',
        }),
      }
    )

    if (!response.ok) {
      const error = await response.json()
      console.error('Telegram API error:', error)
      return false
    }

    return true
  } catch (error) {
    console.error('Failed to send Telegram notification:', error)
    return false
  }
}

// Симуляция отправки SMS/Email
async function sendConfirmation(
  phoneOrContract: string,
  warrantyId: string
) {
  // TODO: Интеграция с SMS.ru, UniSender и т.д.
  console.log('Sending confirmation:', { phoneOrContract, warrantyId })
  return true
}

export async function POST(request: NextRequest) {
  try {
    // Rate limiting
    const ip = getClientIP(request)
    if (!checkRateLimit(ip)) {
      return NextResponse.json(
        { error: 'Превышен лимит запросов. Попробуйте позже.' },
        { status: 429 }
      )
    }

    // Парсинг и валидация тела запроса
    const body = await request.json()
    const validatedData = activateRequestSchema.parse(body)

    // Валидация договора/телефона
    const contractValidation = await validateContractOrPhone(
      validatedData.phone_or_contract
    )

    if (!contractValidation.valid) {
      return NextResponse.json(
        {
          error:
            'Не найден договор или телефон. Проверьте ввод или свяжитесь с поддержкой.',
        },
        { status: 404 }
      )
    }

    // Проверка, не была ли уже активирована гарантия
    if (contractValidation.contractId) {
      const alreadyActivated = await checkAlreadyActivated(
        contractValidation.contractId
      )
      if (alreadyActivated) {
        return NextResponse.json(
          {
            error: 'Гарантия уже была активирована ранее.',
            activated: true,
          },
          { status: 409 }
        )
      }
    }

    // Генерация ID гарантийного талона
    const warrantyId = generateWarrantyId()

    // Расчет срока действия скидок (14 дней)
    const discountsReservedUntil = new Date()
    discountsReservedUntil.setDate(discountsReservedUntil.getDate() + 14)

    // Подготовка данных для сохранения
    const warrantyData = {
      warranty_id: warrantyId,
      contract_id: contractValidation.contractId || validatedData.phone_or_contract,
      phone_or_contract: validatedData.phone_or_contract,
      has_extra_work: validatedData.has_extra_work,
      extra_work: validatedData.extra_work,
      rates: validatedData.rates,
      discounts: validatedData.discounts,
      activated_at: new Date().toISOString(),
      activated_by_ip: ip,
      discounts_reserved_until: discountsReservedUntil.toISOString(),
    }

    // Сохранение в CRM/БД
    await saveToCRM(warrantyData)

    // Отправка подтверждения
    await sendConfirmation(
      validatedData.phone_or_contract,
      warrantyId
    )

    // Отправка уведомления в Telegram
    await sendTelegramNotification({
      warrantyId,
      phoneOrContract: validatedData.phone_or_contract,
      hasExtraWork: validatedData.has_extra_work,
      extraWork: validatedData.extra_work,
      rates: validatedData.rates,
      discounts: validatedData.discounts,
      discountsReservedUntil: discountsReservedUntil.toISOString().split('T')[0],
    })

    // Возврат успешного ответа
    return NextResponse.json({
      activated: true,
      warranty_id: warrantyId,
      contract_id:
        contractValidation.contractId || validatedData.phone_or_contract,
      discounts_reserved_until: discountsReservedUntil.toISOString().split('T')[0],
    })
  } catch (error: any) {
    console.error('Warranty activation error:', error)

    if (error instanceof z.ZodError) {
      return NextResponse.json(
        {
          error: 'Ошибка валидации данных',
          details: error.errors,
        },
        { status: 400 }
      )
    }

    return NextResponse.json(
      {
        error: error.message || 'Внутренняя ошибка сервера',
      },
      { status: 500 }
    )
  }
}
