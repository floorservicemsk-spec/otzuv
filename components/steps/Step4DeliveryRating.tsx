'use client';

import React from 'react';
import { Control, Controller, FieldErrors } from 'react-hook-form';
import { WarrantyFormSchema } from '@/lib/validation';
import RatingScale from '../RatingScale';
import ReviewLink from '../ReviewLink';

interface Step4Props {
  control: Control<WarrantyFormSchema>;
  errors: FieldErrors<WarrantyFormSchema>;
}

export default function Step4DeliveryRating({ control, errors }: Step4Props) {
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
          Работа доставки
        </h2>
        <p className="text-gray-600">
          Оцените качество работы службы доставки
        </p>
      </div>
      
      <Controller
        name="delivery_rate"
        control={control}
        render={({ field }) => (
          <RatingScale
            value={field.value}
            onChange={field.onChange}
            label="Оцените работу доставки"
            name="delivery_rate"
            error={errors.delivery_rate?.message}
          />
        )}
      />
      
      <div className="pt-4 border-t border-gray-200">
        <ReviewLink source="delivery" />
      </div>
      
      <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p className="text-sm text-blue-800">
          💡 Ваша оценка помогает нам контролировать качество доставки. 
          Этот вопрос необязательный.
        </p>
      </div>
    </div>
  );
}
