'use client';

import React from 'react';
import { UseFormRegister, FieldErrors, Control, useFieldArray } from 'react-hook-form';
import { WarrantyFormSchema } from '@/lib/validation';
import { Plus, Trash2 } from 'lucide-react';

interface Step2Props {
  register: UseFormRegister<WarrantyFormSchema>;
  errors: FieldErrors<WarrantyFormSchema>;
  control: Control<WarrantyFormSchema>;
  hasExtraWork: boolean;
  onHasExtraWorkChange: (value: boolean) => void;
}

export default function Step2ExtraWork({ 
  register, 
  errors, 
  control, 
  hasExtraWork, 
  onHasExtraWorkChange 
}: Step2Props) {
  const { fields, append, remove } = useFieldArray({
    control,
    name: 'extra_work',
  });
  
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
          Дополнительные работы
        </h2>
        <p className="text-gray-600">
          Были ли выполнены дополнительные работы, не вошедшие в договор?
        </p>
      </div>
      
      <div className="flex gap-4">
        <button
          type="button"
          onClick={() => {
            onHasExtraWorkChange(true);
            if (fields.length === 0) {
              append({ title: '', price: undefined });
            }
          }}
          className={`flex-1 px-6 py-4 rounded-lg border-2 transition-all duration-200 font-medium ${
            hasExtraWork
              ? 'border-primary-600 bg-primary-50 text-primary-900'
              : 'border-gray-300 bg-white text-gray-700 hover:border-primary-400'
          }`}
        >
          Да
        </button>
        <button
          type="button"
          onClick={() => {
            onHasExtraWorkChange(false);
            // Clear all fields
            while (fields.length > 0) {
              remove(0);
            }
          }}
          className={`flex-1 px-6 py-4 rounded-lg border-2 transition-all duration-200 font-medium ${
            !hasExtraWork
              ? 'border-primary-600 bg-primary-50 text-primary-900'
              : 'border-gray-300 bg-white text-gray-700 hover:border-primary-400'
          }`}
        >
          Нет
        </button>
      </div>
      
      {hasExtraWork && (
        <div className="space-y-4 mt-6">
          <h3 className="text-lg font-medium text-gray-900">
            Укажите выполненные работы
          </h3>
          
          {fields.map((field, index) => (
            <div key={field.id} className="flex gap-3 items-start">
              <div className="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <input
                    {...register(`extra_work.${index}.title`)}
                    placeholder="Описание работ"
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                  />
                  {errors.extra_work?.[index]?.title && (
                    <p className="mt-1 text-sm text-red-600">
                      {errors.extra_work[index]?.title?.message}
                    </p>
                  )}
                </div>
                <div>
                  <input
                    {...register(`extra_work.${index}.price`, { valueAsNumber: true })}
                    type="number"
                    placeholder="Стоимость (необязательно)"
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                  />
                </div>
              </div>
              <button
                type="button"
                onClick={() => remove(index)}
                className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                aria-label="Удалить работу"
              >
                <Trash2 className="w-5 h-5" />
              </button>
            </div>
          ))}
          
          <button
            type="button"
            onClick={() => append({ title: '', price: undefined })}
            className="flex items-center gap-2 px-4 py-2 text-primary-600 hover:text-primary-700 font-medium transition-colors"
          >
            <Plus className="w-5 h-5" />
            Добавить еще
          </button>
        </div>
      )}
    </div>
  );
}
