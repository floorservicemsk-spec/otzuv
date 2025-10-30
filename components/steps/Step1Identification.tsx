'use client';

import React from 'react';
import { UseFormRegister, FieldErrors } from 'react-hook-form';
import { WarrantyFormSchema } from '@/lib/validation';
import { formatPhone } from '@/lib/validation';

interface Step1Props {
  register: UseFormRegister<WarrantyFormSchema>;
  errors: FieldErrors<WarrantyFormSchema>;
  value: string;
  onChange: (value: string) => void;
}

export default function Step1Identification({ register, errors, value, onChange }: Step1Props) {
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const inputValue = e.target.value;
    
    // If starts with + or digit, apply phone formatting
    if (inputValue.startsWith('+') || /^\d/.test(inputValue)) {
      onChange(formatPhone(inputValue));
    } else {
      onChange(inputValue);
    }
  };
  
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
          Активация гарантийного талона
        </h2>
        <p className="text-gray-600">
          Для активации заполните анкету. Гарантия активируется автоматически.
        </p>
      </div>
      
      <div>
        <label htmlFor="phone_or_contract" className="block text-lg font-medium text-gray-900 mb-2">
          Телефон или номер договора <span className="text-red-500">*</span>
        </label>
        <input
          id="phone_or_contract"
          type="text"
          {...register('phone_or_contract')}
          value={value}
          onChange={handleChange}
          placeholder="+7 (___) ___-__-__ или IL-123456"
          className="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg"
          aria-required="true"
          aria-invalid={!!errors.phone_or_contract}
          aria-describedby={errors.phone_or_contract ? 'phone-error' : undefined}
        />
        {errors.phone_or_contract && (
          <p id="phone-error" className="mt-2 text-sm text-red-600" role="alert">
            {errors.phone_or_contract.message}
          </p>
        )}
        <p className="mt-2 text-sm text-gray-500">
          Введите номер телефона в формате +7 (XXX) XXX-XX-XX или номер договора (IL-, D- или цифры)
        </p>
      </div>
    </div>
  );
}
