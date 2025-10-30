'use client'

import React, { useState } from 'react'
import InputMask from 'react-input-mask'
import clsx from 'clsx'

interface PhoneInputProps {
  value: string
  onChange: (value: string) => void
  error?: string
  required?: boolean
  placeholder?: string
  className?: string
}

export function PhoneInput({
  value,
  onChange,
  error,
  required = false,
  placeholder = '+7 (___) ___-__-__',
  className,
}: PhoneInputProps) {
  const [focused, setFocused] = useState(false)

  return (
    <div className={className}>
      <div className="relative">
        <InputMask
          mask="+7 (999) 999-99-99"
          value={value}
          onChange={(e) => onChange(e.target.value)}
          onFocus={() => setFocused(true)}
          onBlur={() => setFocused(false)}
          placeholder={placeholder}
          className={clsx(
            'w-full px-4 py-3 border rounded-lg text-base',
            'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent',
            'transition-all duration-200',
            error
              ? 'border-red-500 bg-red-50'
              : focused
              ? 'border-primary-500'
              : 'border-gray-300',
            'placeholder:text-gray-400'
          )}
          required={required}
          aria-invalid={!!error}
          aria-describedby={error ? 'phone-error' : undefined}
        />
      </div>
      {error && (
        <p
          id="phone-error"
          className="mt-2 text-sm text-red-600"
          role="alert"
        >
          {error}
        </p>
      )}
    </div>
  )
}

interface TextInputProps {
  value: string
  onChange: (value: string) => void
  error?: string
  required?: boolean
  placeholder?: string
  label?: string
  className?: string
  type?: 'text' | 'email' | 'tel'
}

export function TextInput({
  value,
  onChange,
  error,
  required = false,
  placeholder,
  label,
  className,
  type = 'text',
}: TextInputProps) {
  const [focused, setFocused] = useState(false)

  return (
    <div className={className}>
      {label && (
        <label className="block text-sm font-medium text-gray-700 mb-2">
          {label}
          {required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}
      <input
        type={type}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        onFocus={() => setFocused(true)}
        onBlur={() => setFocused(false)}
        placeholder={placeholder}
        required={required}
        className={clsx(
          'w-full px-4 py-3 border rounded-lg text-base',
          'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent',
          'transition-all duration-200',
          error
            ? 'border-red-500 bg-red-50'
            : focused
            ? 'border-primary-500'
            : 'border-gray-300',
          'placeholder:text-gray-400'
        )}
        aria-invalid={!!error}
        aria-describedby={error ? `${type}-error` : undefined}
      />
      {error && (
        <p
          id={`${type}-error`}
          className="mt-2 text-sm text-red-600"
          role="alert"
        >
          {error}
        </p>
      )}
    </div>
  )
}
