'use client';

import React from 'react';
import { Check } from 'lucide-react';
import clsx from 'clsx';

interface DiscountCardProps {
  id: string;
  title: string;
  discount: number;
  selected: boolean;
  onToggle: () => void;
}

export default function DiscountCard({ id, title, discount, selected, onToggle }: DiscountCardProps) {
  const isNone = id === 'none';
  
  return (
    <button
      type="button"
      onClick={onToggle}
      className={clsx(
        'relative p-4 sm:p-6 rounded-xl border-2 transition-all duration-200',
        'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
        'hover:shadow-lg hover:scale-105',
        'text-left w-full',
        selected
          ? 'border-primary-600 bg-primary-50 shadow-md'
          : 'border-gray-300 bg-white'
      )}
      aria-pressed={selected}
    >
      {selected && (
        <div className="absolute top-3 right-3 w-6 h-6 bg-primary-600 rounded-full flex items-center justify-center">
          <Check className="w-4 h-4 text-white" />
        </div>
      )}
      
      <div className="pr-8">
        <h3 className="font-semibold text-gray-900 mb-1">
          {title}
        </h3>
        {!isNone && (
          <div className="text-2xl font-bold text-primary-600">
            −{discount}%
          </div>
        )}
        {isNone && (
          <div className="text-sm text-gray-600">
            Не нужны дополнительные товары
          </div>
        )}
      </div>
    </button>
  );
}
