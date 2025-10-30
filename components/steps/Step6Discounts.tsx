'use client';

import React from 'react';
import { Control, Controller } from 'react-hook-form';
import { WarrantyFormSchema } from '@/lib/validation';
import { DISCOUNT_OPTIONS } from '@/types/warranty';
import DiscountCard from '../DiscountCard';
import { analyticsEvents } from '@/lib/analytics';

interface Step6Props {
  control: Control<WarrantyFormSchema>;
}

export default function Step6Discounts({ control }: Step6Props) {
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
          Бронирование скидок
        </h2>
        <p className="text-gray-600">
          Зафиксируйте специальные скидки на сопутствующие товары и услуги. 
          Скидки будут действовать 14 дней.
        </p>
      </div>
      
      <Controller
        name="discounts"
        control={control}
        render={({ field }) => {
          const handleToggle = (discountId: string) => {
            const isNone = discountId === 'none';
            const isSelected = field.value.includes(discountId);
            
            let newValue: string[];
            
            if (isNone) {
              // If "none" is clicked, clear all other selections
              newValue = isSelected ? [] : ['none'];
            } else {
              // If any other option is clicked, remove "none" if present
              const filtered = field.value.filter(id => id !== 'none');
              
              if (isSelected) {
                // Deselect
                newValue = filtered.filter(id => id !== discountId);
              } else {
                // Select
                newValue = [...filtered, discountId];
                analyticsEvents.discountSelected(discountId);
              }
            }
            
            field.onChange(newValue);
          };
          
          return (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {DISCOUNT_OPTIONS.map((option) => (
                <DiscountCard
                  key={option.id}
                  id={option.id}
                  title={option.title}
                  discount={option.discount}
                  selected={field.value.includes(option.id)}
                  onToggle={() => handleToggle(option.id)}
                />
              ))}
            </div>
          );
        }}
      />
      
      <div className="bg-green-50 border border-green-200 rounded-lg p-4">
        <p className="text-sm text-green-800">
          ✨ Выбранные скидки будут зарезервированы за вами на 14 дней. 
          Вы сможете воспользоваться ими при следующей покупке.
        </p>
      </div>
    </div>
  );
}
