'use client';

import React from 'react';
import { Control, Controller, FieldErrors } from 'react-hook-form';
import { WarrantyFormSchema } from '@/lib/validation';
import RatingScale from '../RatingScale';
import ReviewLink from '../ReviewLink';

interface Step3Props {
  control: Control<WarrantyFormSchema>;
  errors: FieldErrors<WarrantyFormSchema>;
}

export default function Step3SalesRating({ control, errors }: Step3Props) {
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
          Работа продавцов
        </h2>
        <p className="text-gray-600">
          Оцените качество обслуживания наших продавцов
        </p>
      </div>
      
      <Controller
        name="sales_rate"
        control={control}
        render={({ field }) => (
          <RatingScale
            value={field.value}
            onChange={field.onChange}
            label="Оцените работу продавцов"
            name="sales_rate"
            error={errors.sales_rate?.message}
          />
        )}
      />
      
      <div className="pt-4 border-t border-gray-200">
        <ReviewLink source="sales" />
      </div>
      
      <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p className="text-sm text-blue-800">
          💡 Ваша оценка поможет нам улучшить качество обслуживания. 
          Этот вопрос необязательный, но мы будем благодарны за обратную связь.
        </p>
      </div>
    </div>
  );
}
