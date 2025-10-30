'use client'

import React, { useState } from 'react'
import clsx from 'clsx'

interface PrivacyCheckboxProps {
  value: boolean
  onChange: (value: boolean) => void
  error?: string
  className?: string
}

export function PrivacyCheckbox({
  value,
  onChange,
  error,
  className,
}: PrivacyCheckboxProps) {
  return (
    <div className={className}>
      <label className="flex items-start gap-3 cursor-pointer">
        <input
          type="checkbox"
          checked={value}
          onChange={(e) => onChange(e.target.checked)}
          className="mt-1 w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
          required
          aria-invalid={!!error}
          aria-describedby={error ? 'privacy-error' : undefined}
        />
        <span className="text-sm text-gray-700">
          Я согласен с{' '}
          <a
            href="/privacy-policy"
            target="_blank"
            rel="noopener noreferrer"
            className="text-primary-600 hover:text-primary-700 underline"
          >
            политикой конфиденциальности
          </a>{' '}
          и обработкой персональных данных
        </span>
      </label>
      {error && (
        <p
          id="privacy-error"
          className="mt-2 text-sm text-red-600"
          role="alert"
        >
          {error}
        </p>
      )}
    </div>
  )
}
