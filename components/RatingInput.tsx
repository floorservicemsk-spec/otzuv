'use client'

import React from 'react'
import clsx from 'clsx'
import { RATING_LABELS } from '@/lib/schemas'

interface RatingInputProps {
  value: number | null | undefined
  onChange: (value: number) => void
  error?: string
  label: string
  required?: boolean
  className?: string
}

export function RatingInput({
  value,
  onChange,
  error,
  label,
  required = false,
  className,
}: RatingInputProps) {
  const ratings = [1, 2, 3, 4, 5] as const

  return (
    <div className={className}>
      <label className="block text-base font-medium text-gray-900 mb-4">
        {label}
        {required && <span className="text-red-500 ml-1">*</span>}
      </label>
      
      <div className="flex flex-wrap gap-3 sm:gap-4">
        {ratings.map((rating) => (
          <button
            key={rating}
            type="button"
            onClick={() => onChange(rating)}
            className={clsx(
              'flex-1 min-w-[60px] sm:min-w-[80px] px-3 py-3 sm:py-4',
              'border-2 rounded-lg text-sm sm:text-base font-medium',
              'transition-all duration-200',
              'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
              value === rating
                ? 'bg-primary-500 text-white border-primary-500'
                : 'bg-white text-gray-700 border-gray-300 hover:border-primary-300 hover:bg-primary-50',
              'touch-manipulation'
            )}
            aria-label={`Оценка ${rating} из 5: ${RATING_LABELS[rating]}`}
            aria-pressed={value === rating}
          >
            <div className="font-semibold">{rating}</div>
            <div className="text-xs mt-1 opacity-90">
              {RATING_LABELS[rating]}
            </div>
          </button>
        ))}
      </div>

      {error && (
        <p className="mt-3 text-sm text-red-600" role="alert">
          {error}
        </p>
      )}

      {value && (
        <div className="mt-3">
          <a
            href="https://yandex.ru/maps/org/i_laminat/..." // Заменить на реальный URL
            target="_blank"
            rel="noopener noreferrer"
            className="text-sm text-primary-600 hover:text-primary-700 underline"
            onClick={() => {
              if (typeof window !== 'undefined' && window.gtag) {
                window.gtag('event', 'review_link_clicked', {
                  source: 'rating_block',
                  rating: value,
                })
              }
            }}
          >
            Оставить отзыв на Яндекс.Отзывы →
          </a>
        </div>
      )}
    </div>
  )
}
