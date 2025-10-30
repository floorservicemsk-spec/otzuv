'use client'

import React, { useState, useEffect } from 'react'
import { useForm, Controller } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { useRouter } from 'next/navigation'
import {
  warrantyFormSchema,
  WarrantyFormData,
  RATING_LABELS,
} from '@/lib/schemas'
import { PhoneInput, TextInput } from '@/components/Input'
import { RatingInput } from '@/components/RatingInput'
import { ExtraWorkInput } from '@/components/ExtraWorkInput'
import { DiscountSelection } from '@/components/DiscountSelection'
import { PrivacyCheckbox } from '@/components/PrivacyCheckbox'
import { saveDraft, loadDraft, clearDraft, trackEvent } from '@/lib/utils'
import { isPhone, isContract } from '@/lib/utils'

// Шаги формы
const STEP_IDENTIFICATION = 1
const STEP_EXTRA_WORK = 2
const STEP_SALES = 3
const STEP_DELIVERY = 4
const STEP_INSTALLATION = 5
const STEP_DISCOUNTS = 6

const TOTAL_STEPS_WITHOUT_INSTALLATION = 5
const TOTAL_STEPS_WITH_INSTALLATION = 6

interface ProgressBarProps {
  currentStep: number
  totalSteps: number
}

function ProgressBar({ currentStep, totalSteps }: ProgressBarProps) {
  const percentage = (currentStep / totalSteps) * 100

  return (
    <div className="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200">
      <div className="max-w-4xl mx-auto px-4 py-3">
        <div className="flex items-center justify-between mb-2">
          <span className="text-sm font-medium text-gray-700">
            Шаг {currentStep} из {totalSteps}
          </span>
          <span className="text-sm text-gray-500">
            {Math.round(percentage)}%
          </span>
        </div>
        <div className="w-full bg-gray-200 rounded-full h-2">
          <div
            className="bg-primary-500 h-2 rounded-full transition-all duration-300"
            style={{ width: `${percentage}%` }}
          />
        </div>
      </div>
    </div>
  )
}

function getTotalSteps(hasInstallation: boolean | null): number {
  return hasInstallation === false ? TOTAL_STEPS_WITHOUT_INSTALLATION : TOTAL_STEPS_WITH_INSTALLATION
}

function getStepNumber(step: number, hasInstallation: boolean | null): number {
  // Если шаг монтажников пропускается, нужно скорректировать нумерацию
  if (!hasInstallation && step >= STEP_INSTALLATION) {
    return step - 1
  }
  return step
}

function getStepTitle(step: number, hasInstallation: boolean | null): number {
  // Для отображения номера шага в заголовке
  return getStepNumber(step, hasInstallation)
}

export function WarrantyForm() {
  const router = useRouter()
  const [currentStep, setCurrentStep] = useState(1)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [submitError, setSubmitError] = useState<string | null>(null)
  const [hasInstallation, setHasInstallation] = useState<boolean | null>(null)
  const [privacyAccepted, setPrivacyAccepted] = useState(false)

  const {
    register,
    control,
    handleSubmit,
    watch,
    setValue,
    formState: { errors, isValid },
    trigger,
  } = useForm<WarrantyFormData>({
    resolver: zodResolver(warrantyFormSchema),
    mode: 'onChange',
    defaultValues: {
      phone_or_contract: '',
      has_extra_work: false,
      extra_work: [],
      discounts: [],
    },
  })

  const phoneOrContract = watch('phone_or_contract')
  const hasExtraWork = watch('has_extra_work')
  const extraWork = watch('extra_work')

  // Определение наличия монтажа на основе договора
  useEffect(() => {
    const checkInstallation = async () => {
      if (!phoneOrContract || !isContract(phoneOrContract)) {
        setHasInstallation(null)
        return
      }

      try {
        // TODO: Реальная проверка через API
        // Пока симуляция: если договор начинается с IL-1, то есть монтаж
        const normalized = phoneOrContract.replace(/\s/g, '').toUpperCase()
        setHasInstallation(normalized.startsWith('IL-1'))
      } catch (error) {
        setHasInstallation(null)
      }
    }

    checkInstallation()
  }, [phoneOrContract])

  // Загрузка драфта при монтировании
  useEffect(() => {
    const draft = loadDraft()
    if (draft) {
      Object.keys(draft).forEach((key) => {
        setValue(key as keyof WarrantyFormData, draft[key])
      })
    }
  }, [setValue])

  // Сохранение драфта при изменении
  useEffect(() => {
    const subscription = watch((data) => {
      saveDraft(data)
    })
    return () => subscription.unsubscribe()
  }, [watch])

  // Отслеживание начала заполнения формы
  useEffect(() => {
    trackEvent('warranty_start')
  }, [])

  // Отслеживание шагов
  useEffect(() => {
    trackEvent('id_step_completed', { step: currentStep })
  }, [currentStep])

  const totalSteps = getTotalSteps(hasInstallation)

  const nextStep = async () => {
    // Валидация текущего шага
    let fieldsToValidate: (keyof WarrantyFormData)[] = []

    switch (currentStep) {
      case STEP_IDENTIFICATION:
        fieldsToValidate = ['phone_or_contract']
        break
      case STEP_EXTRA_WORK:
        fieldsToValidate = ['has_extra_work', 'extra_work']
        break
      case STEP_SALES:
        fieldsToValidate = ['sales_rate']
        break
      case STEP_DELIVERY:
        fieldsToValidate = ['delivery_rate']
        break
      case STEP_INSTALLATION:
        if (hasInstallation) {
          fieldsToValidate = ['installation_rate']
        }
        break
      case STEP_DISCOUNTS:
        fieldsToValidate = ['discounts']
        break
    }

    const isStepValid = await trigger(fieldsToValidate)

    if (isStepValid) {
      let nextStepNum = currentStep + 1
      
      // Пропускаем шаг монтажников, если монтажа нет
      if (nextStepNum === STEP_INSTALLATION && hasInstallation === false) {
        nextStepNum = STEP_DISCOUNTS
      }

      if (nextStepNum <= totalSteps) {
        setCurrentStep(nextStepNum)
        window.scrollTo({ top: 0, behavior: 'smooth' })
      }
    }
  }

  const prevStep = () => {
    if (currentStep <= STEP_IDENTIFICATION) return

    let prevStepNum = currentStep - 1
    
    // Пропускаем шаг монтажников при возврате назад, если монтажа нет
    if (prevStepNum === STEP_INSTALLATION && hasInstallation === false) {
      prevStepNum = STEP_DELIVERY
    }

    setCurrentStep(prevStepNum)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }

  const onSubmit = async (data: WarrantyFormData) => {
    if (!privacyAccepted) {
      setSubmitError('Необходимо согласие с политикой конфиденциальности')
      return
    }

    setIsSubmitting(true)
    setSubmitError(null)

    try {
      // Нормализация данных для API
      const requestData = {
        phone_or_contract: data.phone_or_contract,
        has_extra_work: data.has_extra_work,
        extra_work: data.extra_work,
        rates: {
          sales: data.sales_rate ?? null,
          delivery: data.delivery_rate ?? null,
          installation: data.installation_rate ?? null,
        },
        discounts: data.discounts,
      }

      trackEvent('warranty_submit')

      const response = await fetch('/api/warranty/activate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      })

      const result = await response.json()

      if (!response.ok) {
        // Обработка специфичных ошибок
        if (response.status === 409) {
          // Гарантия уже активирована
          throw new Error(result.error || 'Гарантия уже была активирована ранее.')
        } else if (response.status === 404) {
          // Договор не найден
          throw new Error(
            result.error ||
              'Не найден договор или телефон. Проверьте ввод или свяжитесь с поддержкой.'
          )
        } else if (response.status === 429) {
          // Rate limit
          throw new Error(
            result.error ||
              'Превышен лимит запросов. Попробуйте позже.'
          )
        } else {
          throw new Error(result.error || 'Ошибка активации гарантии')
        }
      }

      trackEvent('warranty_activated', {
        warranty_id: result.warranty_id,
        contract_id: result.contract_id,
      })

      clearDraft()
      router.push(`/garantia/success?warranty_id=${result.warranty_id}`)
    } catch (error: any) {
      console.error('Submit error:', error)
      
      // Обработка сетевых ошибок
      if (error.name === 'TypeError' && error.message.includes('fetch')) {
        setSubmitError(
          'Ошибка подключения к серверу. Проверьте интернет-соединение и попробуйте еще раз.'
        )
      } else {
        setSubmitError(error.message || 'Произошла ошибка. Попробуйте еще раз.')
      }
      
      trackEvent('warranty_submit_error', { error: error.message })
    } finally {
      setIsSubmitting(false)
    }
  }

  const canProceed = currentStep === 1 ? isValid : true

  const displayStep = getStepNumber(currentStep, hasInstallation)

  return (
    <>
      <ProgressBar currentStep={displayStep} totalSteps={totalSteps} />
      
      <div className="pt-20 pb-12 min-h-screen bg-gray-50">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="bg-white rounded-lg shadow-sm p-6 sm:p-8 md:p-10">
            <h1 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
              Активация гарантийного талона
            </h1>
            <p className="text-base text-gray-600 mb-8">
              Для активации заполните анкету. Гарантия активируется автоматически.
            </p>

            <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
              {/* Шаг 1: Идентификация */}
              {currentStep === STEP_IDENTIFICATION && (
                <div className="space-y-6">
                  <h2 className="text-xl font-semibold text-gray-900">
                    {getStepTitle(currentStep, hasInstallation)}. Идентификация
                  </h2>
                  <Controller
                    name="phone_or_contract"
                    control={control}
                    render={({ field, fieldState }) => (
                      <div>
                        {isPhone(field.value) ? (
                          <PhoneInput
                            value={field.value}
                            onChange={field.onChange}
                            error={fieldState.error?.message}
                            required
                          />
                        ) : (
                          <TextInput
                            value={field.value}
                            onChange={field.onChange}
                            error={fieldState.error?.message}
                            required
                            placeholder="+7 (___) ___-__-__ или IL-123456"
                            label="Телефон или номер договора"
                          />
                        )}
                      </div>
                    )}
                  />
                </div>
              )}

              {/* Шаг 2: Доп. работы */}
              {currentStep === STEP_EXTRA_WORK && (
                <div className="space-y-6">
                  <h2 className="text-xl font-semibold text-gray-900">
                    {getStepTitle(currentStep, hasInstallation)}. Дополнительные работы
                  </h2>
                  <Controller
                    name="has_extra_work"
                    control={control}
                    render={({ field }) => (
                      <ExtraWorkInput
                        hasExtraWork={field.value}
                        onHasExtraWorkChange={field.onChange}
                        value={extraWork}
                        onChange={(value) => setValue('extra_work', value)}
                        error={errors.extra_work?.message}
                      />
                    )}
                  />
                </div>
              )}

              {/* Шаг 3: Работа продавцов */}
              {currentStep === STEP_SALES && (
                <div className="space-y-6">
                  <h2 className="text-xl font-semibold text-gray-900">
                    {getStepTitle(currentStep, hasInstallation)}. Работа продавцов
                  </h2>
                  <Controller
                    name="sales_rate"
                    control={control}
                    render={({ field, fieldState }) => (
                      <RatingInput
                        value={field.value}
                        onChange={field.onChange}
                        error={fieldState.error?.message}
                        label="Оцените работу продавцов"
                      />
                    )}
                  />
                </div>
              )}

              {/* Шаг 4: Работа доставки */}
              {currentStep === STEP_DELIVERY && (
                <div className="space-y-6">
                  <h2 className="text-xl font-semibold text-gray-900">
                    {getStepTitle(currentStep, hasInstallation)}. Работа доставки
                  </h2>
                  <Controller
                    name="delivery_rate"
                    control={control}
                    render={({ field, fieldState }) => (
                      <RatingInput
                        value={field.value}
                        onChange={field.onChange}
                        error={fieldState.error?.message}
                        label="Оцените работу доставки"
                      />
                    )}
                  />
                </div>
              )}

              {/* Шаг 5: Работа монтажников */}
              {currentStep === STEP_INSTALLATION && hasInstallation === true && (
                <div className="space-y-6">
                  <h2 className="text-xl font-semibold text-gray-900">
                    {getStepTitle(currentStep, hasInstallation)}. Работа монтажников
                  </h2>
                  <Controller
                    name="installation_rate"
                    control={control}
                    render={({ field, fieldState }) => (
                      <RatingInput
                        value={field.value}
                        onChange={field.onChange}
                        error={fieldState.error?.message}
                        label="Оцените работу монтажников"
                      />
                    )}
                  />
                </div>
              )}

              {/* Шаг 6: Бронирование скидок */}
              {currentStep === STEP_DISCOUNTS && (
                <div className="space-y-6">
                  <h2 className="text-xl font-semibold text-gray-900">
                    {getStepTitle(currentStep, hasInstallation)}. Бронирование скидок
                  </h2>
                  <Controller
                    name="discounts"
                    control={control}
                    render={({ field, fieldState }) => (
                      <DiscountSelection
                        value={field.value}
                        onChange={(value) => {
                          field.onChange(value)
                          value.forEach((code) => {
                            if (code !== 'none') {
                              trackEvent('discount_selected', { code })
                            }
                          })
                        }}
                        error={fieldState.error?.message}
                      />
                    )}
                  />
                </div>
              )}

              {/* Кнопки навигации */}
              <div className="flex flex-col sm:flex-row justify-between gap-4 pt-6 border-t border-gray-200">
                <button
                  type="button"
                  onClick={prevStep}
                  disabled={currentStep === 1}
                  className="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  Назад
                </button>

                {currentStep < totalSteps ? (
                  <button
                    type="button"
                    onClick={nextStep}
                    disabled={!canProceed}
                    className="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    Далее
                  </button>
                ) : (
                  <button
                    type="submit"
                    disabled={isSubmitting || !privacyAccepted}
                    className="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    {isSubmitting ? 'Отправка...' : 'Активировать гарантию'}
                  </button>
                )}
              </div>

              {submitError && (
                <div className="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                  <p className="text-red-800 text-sm mb-2">{submitError}</p>
                  <button
                    type="button"
                    onClick={() => handleSubmit(onSubmit)()}
                    className="text-sm text-red-700 hover:text-red-800 underline font-medium"
                  >
                    Попробовать еще раз
                  </button>
                </div>
              )}

              {/* Чекбокс согласия с политикой конфиденциальности */}
              {currentStep === STEP_DISCOUNTS && (
                <div className="pt-4">
                  <PrivacyCheckbox
                    value={privacyAccepted}
                    onChange={setPrivacyAccepted}
                  />
                </div>
              )}
            </form>
          </div>
        </div>
      </div>
    </>
  )
}
