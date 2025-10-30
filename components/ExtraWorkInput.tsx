'use client'

import React, { useState } from 'react'
import clsx from 'clsx'
import { TextInput } from './Input'

interface ExtraWorkItem {
  title: string
  price?: number | null
}

interface ExtraWorkInputProps {
  value: ExtraWorkItem[]
  onChange: (value: ExtraWorkItem[]) => void
  hasExtraWork: boolean
  onHasExtraWorkChange: (has: boolean) => void
  error?: string
  className?: string
}

export function ExtraWorkInput({
  value,
  onChange,
  hasExtraWork,
  onHasExtraWorkChange,
  error,
  className,
}: ExtraWorkInputProps) {
  const addWork = () => {
    onChange([...value, { title: '', price: null }])
  }

  const updateWork = (index: number, updates: Partial<ExtraWorkItem>) => {
    const updated = [...value]
    updated[index] = { ...updated[index], ...updates }
    onChange(updated)
  }

  const removeWork = (index: number) => {
    onChange(value.filter((_, i) => i !== index))
  }

  return (
    <div className={className}>
      <label className="block text-base font-medium text-gray-900 mb-4">
        Дополнительные работы, не вошедшие в договор
      </label>

      <div className="space-y-4">
        <div className="flex items-center gap-4">
          <label className="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              checked={hasExtraWork}
              onChange={(e) => onHasExtraWorkChange(e.target.checked)}
              className="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
            />
            <span className="text-base text-gray-700">Да</span>
          </label>
        </div>

        {hasExtraWork && (
          <div className="space-y-4 pl-7 border-l-2 border-gray-200">
            {value.length === 0 && (
              <button
                type="button"
                onClick={addWork}
                className="text-primary-600 hover:text-primary-700 underline text-sm font-medium"
              >
                Добавить работу
              </button>
            )}

            {value.map((work, index) => (
              <div
                key={index}
                className="p-4 bg-gray-50 rounded-lg space-y-3"
              >
                <div className="flex items-start justify-between gap-3">
                  <div className="flex-1">
                    <TextInput
                      value={work.title}
                      onChange={(title) => updateWork(index, { title })}
                      placeholder="Название работы"
                      required
                      className="mb-3"
                    />
                    <TextInput
                      type="text"
                      value={work.price?.toString() || ''}
                      onChange={(val) => {
                        const num = val ? parseFloat(val) : null
                        updateWork(index, { price: num && !isNaN(num) ? num : null })
                      }}
                      placeholder="Цена (необязательно)"
                      className="w-full"
                    />
                  </div>
                  {value.length > 1 && (
                    <button
                      type="button"
                      onClick={() => removeWork(index)}
                      className="mt-2 text-red-600 hover:text-red-700 font-medium text-sm"
                      aria-label="Удалить работу"
                    >
                      ✕
                    </button>
                  )}
                </div>
              </div>
            ))}

            {value.length > 0 && (
              <button
                type="button"
                onClick={addWork}
                className="text-primary-600 hover:text-primary-700 underline text-sm font-medium"
              >
                + Добавить еще
              </button>
            )}
          </div>
        )}
      </div>

      {error && (
        <p className="mt-2 text-sm text-red-600" role="alert">
          {error}
        </p>
      )}
    </div>
  )
}
