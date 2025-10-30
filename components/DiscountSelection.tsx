'use client'

import React from 'react'
import clsx from 'clsx'
import { DISCOUNT_OPTIONS } from '@/lib/schemas'

interface DiscountCardProps {
  code: string
  label: string
  discount: number
  selected: boolean
  onClick: () => void
  image?: string | null
}

function DiscountCard({
  code,
  label,
  discount,
  selected,
  onClick,
  image,
}: DiscountCardProps) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={clsx(
        'relative w-full p-4 border-2 rounded-lg',
        'transition-all duration-200',
        'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
        selected
          ? 'border-primary-500 bg-primary-50'
          : 'border-gray-300 bg-white hover:border-primary-300 hover:bg-gray-50',
        'touch-manipulation'
      )}
      aria-pressed={selected}
      aria-label={`${label}, скидка ${discount}%`}
    >
      {selected && (
        <div className="absolute top-2 right-2 w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center">
          <svg
            className="w-4 h-4 text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M5 13l4 4L19 7"
            />
          </svg>
        </div>
      )}

      {image && (
        <div className="w-full h-32 mb-3 bg-gray-100 rounded overflow-hidden">
          <img
            src={image}
            alt={label}
            className="w-full h-full object-cover"
            onError={(e) => {
              e.currentTarget.style.display = 'none'
            }}
          />
        </div>
      )}

      <div className="text-center">
        <div className="font-semibold text-gray-900 mb-1">{label}</div>
        {discount > 0 && (
          <div className="text-lg font-bold text-primary-600">
            Скидка {discount}%
          </div>
        )}
      </div>
    </button>
  )
}

interface DiscountSelectionProps {
  value: string[]
  onChange: (value: string[]) => void
  error?: string
  className?: string
}

export function DiscountSelection({
  value,
  onChange,
  error,
  className,
}: DiscountSelectionProps) {
  const handleToggle = (code: string) => {
    if (code === 'none') {
      // Если выбрано "Ничего не нужно", снимаем все остальные
      onChange(['none'])
    } else {
      // Убираем "none" если он был выбран, и переключаем выбранную скидку
      const filtered = value.filter((c) => c !== 'none')
      const isSelected = filtered.includes(code)
      
      if (isSelected) {
        onChange(filtered.filter((c) => c !== code))
      } else {
        onChange([...filtered, code])
      }
    }
  }

  return (
    <div className={className}>
      <label className="block text-base font-medium text-gray-900 mb-4">
        Бронирование скидок на сопутствующие товары и услуги
      </label>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {DISCOUNT_OPTIONS.map((option) => (
          <DiscountCard
            key={option.code}
            code={option.code}
            label={option.label}
            discount={option.discount}
            selected={value.includes(option.code)}
            onClick={() => handleToggle(option.code)}
            image={option.image}
          />
        ))}
      </div>

      {error && (
        <p className="mt-3 text-sm text-red-600" role="alert">
          {error}
        </p>
      )}
    </div>
  )
}
