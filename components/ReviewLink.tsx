'use client';

import React from 'react';
import { ExternalLink } from 'lucide-react';
import { analyticsEvents } from '@/lib/analytics';

interface ReviewLinkProps {
  source: string;
}

export default function ReviewLink({ source }: ReviewLinkProps) {
  const handleClick = () => {
    analyticsEvents.reviewLinkClicked(source);
  };
  
  return (
    <a
      href="https://yandex.ru/maps/org/i_laminat/1234567890/reviews"
      target="_blank"
      rel="noopener noreferrer"
      onClick={handleClick}
      className="inline-flex items-center gap-2 text-primary-600 hover:text-primary-700 hover:underline font-medium transition-colors"
    >
      <ExternalLink className="w-4 h-4" />
      Оставить отзыв на Яндекс.Отзывах
    </a>
  );
}
