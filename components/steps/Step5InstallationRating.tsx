'use client';

import React from 'react';
import { Control, Controller, FieldErrors } from 'react-hook-form';
import { WarrantyFormSchema } from '@/lib/validation';
import RatingScale from '../RatingScale';
import ReviewLink from '../ReviewLink';

interface Step5Props {
  control: Control<WarrantyFormSchema>;
  errors: FieldErrors<WarrantyFormSchema>;
  hasInstallation: boolean;
}

export default function Step5InstallationRating({ control, errors, hasInstallation }: Step5Props) {
  if (!hasInstallation) {
    return null;
  }
  
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
          Работа монтажников
        </h2>
        <p className="text-gray-600">
          Оцените качество работы монтажной бригады
        </p>
      </div>
      
      <Controller
        name="installation_rate"
        control={control}
        render={({ field }) => (
          <RatingScale
            value={field.value}
            onChange={field.onChange}
            label="Оцените работу монтажников"
            name="installation_rate"
            error={errors.installation_rate?.message}
          />
        )}
      />
      
      <div className="pt-4 border-t border-gray-200">
        <ReviewLink source="installation" />
      </div>
      
      <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p className="text-sm text-blue-800">
          💡 Ваша оценка помогает нам следить за качеством монтажных работ. 
          Этот вопрос необязательный.
        </p>
      </div>
    </div>
  );
}
