// Утилиты для работы с телефоном и договором
export function normalizePhone(value: string): string {
  // Удаляем все нецифровые символы кроме +7
  const digits = value.replace(/\D/g, '')
  if (digits.startsWith('7') && digits.length === 11) {
    return `+7${digits.slice(1)}`
  }
  if (digits.startsWith('8') && digits.length === 11) {
    return `+7${digits.slice(1)}`
  }
  if (digits.length === 10) {
    return `+7${digits}`
  }
  return value
}

export function normalizeContract(value: string): string {
  // Нормализуем префикс и убираем пробелы
  return value
    .trim()
    .replace(/\s/g, '')
    .toUpperCase()
}

export function isPhone(value: string): boolean {
  const phoneRegex = /^\+7\s?\(\d{3}\)\s?\d{3}-\d{2}-\d{2}$/
  return phoneRegex.test(value)
}

export function isContract(value: string): boolean {
  const contractRegex = /^(IL-|D-)?\d{6,16}$/i
  return contractRegex.test(value.replace(/\s/g, ''))
}

// Утилиты для localStorage (драфт формы)
const DRAFT_KEY = 'warranty_form_draft'
const DRAFT_EXPIRY = 24 * 60 * 60 * 1000 // 24 часа

export function saveDraft(data: any): void {
  if (typeof window === 'undefined') return
  try {
    const draft = {
      data,
      timestamp: Date.now(),
    }
    localStorage.setItem(DRAFT_KEY, JSON.stringify(draft))
  } catch (e) {
    console.error('Failed to save draft:', e)
  }
}

export function loadDraft(): any | null {
  if (typeof window === 'undefined') return null
  try {
    const stored = localStorage.getItem(DRAFT_KEY)
    if (!stored) return null
    
    const draft = JSON.parse(stored)
    const now = Date.now()
    
    // Проверяем срок действия
    if (now - draft.timestamp > DRAFT_EXPIRY) {
      localStorage.removeItem(DRAFT_KEY)
      return null
    }
    
    return draft.data
  } catch (e) {
    console.error('Failed to load draft:', e)
    return null
  }
}

export function clearDraft(): void {
  if (typeof window === 'undefined') return
  localStorage.removeItem(DRAFT_KEY)
}

// Аналитика события
export function trackEvent(eventName: string, params?: Record<string, any>): void {
  if (typeof window === 'undefined') return
  
  // Google Analytics 4
  if (window.gtag) {
    window.gtag('event', eventName, params)
  }
  
  // Яндекс.Метрика
  if (window.ym) {
    window.ym(window.YANDEX_METRICA_ID, 'reachGoal', eventName, params)
  }
}

// Расширяем Window interface
declare global {
  interface Window {
    gtag?: (...args: any[]) => void
    ym?: (...args: any[]) => void
    YANDEX_METRICA_ID?: number
  }
}
