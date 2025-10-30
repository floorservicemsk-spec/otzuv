import { z } from 'zod'

// Схема валидации идентификации
export const phoneOrContractSchema = z
  .string()
  .min(1, 'Поле обязательно для заполнения')
  .refine(
    (val) => {
      // Проверка телефона по маске +7 (___) ___-__-__
      const phoneRegex = /^\+7\s?\(\d{3}\)\s?\d{3}-\d{2}-\d{2}$/
      // Проверка договора: IL-xxxxxx или D-xxxxxx или числовой (6-16 символов)
      const contractRegex = /^(IL-|D-)?\d{6,16}$/i
      
      const normalized = val.replace(/\s/g, '')
      return phoneRegex.test(val) || contractRegex.test(normalized)
    },
    {
      message: 'Введите корректный телефон (+7 (___) ___-__-__) или номер договора',
    }
  )

// Схема для дополнительных работ
export const extraWorkSchema = z.object({
  title: z.string().min(1, 'Название работы обязательно'),
  price: z
    .number()
    .positive('Цена должна быть положительным числом')
    .optional()
    .nullable(),
})

// Схема оценки (1-5)
export const ratingSchema = z
  .number()
  .int()
  .min(1)
  .max(5)
  .optional()
  .nullable()

// Коды скидок
export const discountCodeSchema = z.enum([
  'glue_10',
  'molding_5',
  'underlay_5',
  'primer_10',
  'installation_30',
  'none',
])

// Полная схема формы
export const warrantyFormSchema = z.object({
  phone_or_contract: phoneOrContractSchema,
  has_extra_work: z.boolean().default(false),
  extra_work: z.array(extraWorkSchema).default([]),
  sales_rate: ratingSchema,
  delivery_rate: ratingSchema,
  installation_rate: ratingSchema,
  discounts: z.array(discountCodeSchema).default([]),
})

export type WarrantyFormData = z.infer<typeof warrantyFormSchema>

// Типы для API
export interface WarrantyActivateRequest {
  phone_or_contract: string
  has_extra_work: boolean
  extra_work: Array<{ title: string; price?: number | null }>
  rates: {
    sales?: number | null
    delivery?: number | null
    installation?: number | null
  }
  discounts: string[]
}

export interface WarrantyActivateResponse {
  activated: boolean
  warranty_id: string
  contract_id: string
  discounts_reserved_until: string
}

export interface WarrantyStatusResponse {
  activated: boolean
  warranty_id?: string
  activated_at?: string
}

// Константы
export const RATING_LABELS: Record<number, string> = {
  1: 'Ужасно',
  2: 'Плохо',
  3: 'Не понравилось',
  4: 'Хорошо',
  5: 'Отлично',
}

export const DISCOUNT_OPTIONS = [
  {
    code: 'glue_10',
    label: 'Клей',
    discount: 10,
    image: '/images/glue.jpg',
  },
  {
    code: 'molding_5',
    label: 'Плинтус',
    discount: 5,
    image: '/images/molding.jpg',
  },
  {
    code: 'underlay_5',
    label: 'Подложка',
    discount: 5,
    image: '/images/underlay.jpg',
  },
  {
    code: 'primer_10',
    label: 'Грунтовка',
    discount: 10,
    image: '/images/primer.jpg',
  },
  {
    code: 'installation_30',
    label: 'Укладка',
    discount: 30,
    image: '/images/installation.jpg',
  },
  {
    code: 'none',
    label: 'Ничего не нужно',
    discount: 0,
    image: null,
  },
] as const

export const YANDEX_REVIEWS_URL = 'https://yandex.ru/maps/org/i_laminat/...' // Заменить на реальный URL
