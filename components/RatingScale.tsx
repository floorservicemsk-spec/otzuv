'use client';

import React from 'react';
import { RATING_LABELS } from '@/types/warranty';
import clsx from 'clsx';

interface RatingScaleProps {
  value?: number;
  onChange: (value: number) => void;
  label: string;
  name: string;
  error?: string;
}

export default function RatingScale({ value, onChange, label, name, error }: RatingScaleProps) {
  return (
    <div className="space-y-3">
      <label className="block text-lg font-medium text-gray-900">
        {label}
      </label>
      
      <div className="flex flex-col sm:flex-row gap-2 sm:gap-3">
        {[1, 2, 3, 4, 5].map((rating) => (
          <button
            key={rating}
            type="button"
            onClick={() => onChange(rating)}
            className={clsx(
              'flex-1 px-4 py-3 rounded-lg border-2 transition-all duration-200',
              'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
              'hover:border-primary-400',
              value === rating
                ? 'border-primary-600 bg-primary-50 text-primary-900 font-semibold'
                : 'border-gray-300 bg-white text-gray-700'
            )}
            aria-label={`Оценка ${rating} из 5: ${RATING_LABELS[rating - 1]}`}
            aria-pressed={value === rating}
          >
            <div className="text-center">
              <div className="text-2xl font-bold mb-1">{rating}</div>
              <div className="text-xs sm:text-sm">{RATING_LABELS[rating - 1]}</div>
            </div>
          </button>
        ))}
      </div>
      
      {error && (
        <p className="text-sm text-red-600 mt-1" role="alert">
          {error}
        </p>
      )}
    </div>
  );
}
