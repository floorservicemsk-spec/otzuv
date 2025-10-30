'use client';

import React, { useEffect, useState } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { CheckCircle, Loader2, Home, FileText } from 'lucide-react';

export default function SuccessPage() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const warrantyId = searchParams.get('warranty_id');
  
  const [loading, setLoading] = useState(true);
  const [warrantyData, setWarrantyData] = useState<any>(null);
  const [error, setError] = useState<string | null>(null);
  
  useEffect(() => {
    if (!warrantyId) {
      setError('Номер гарантии не найден');
      setLoading(false);
      return;
    }
    
    // Simulate API call to get warranty details
    setTimeout(() => {
      setWarrantyData({
        warranty_id: warrantyId,
        activated_at: new Date().toISOString(),
        discounts_reserved_until: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toLocaleDateString('ru-RU'),
      });
      setLoading(false);
    }, 1000);
  }, [warrantyId]);
  
  if (loading) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center px-4">
        <div className="text-center">
          <Loader2 className="w-12 h-12 text-primary-600 animate-spin mx-auto mb-4" />
          <p className="text-gray-600">Загрузка данных...</p>
        </div>
      </div>
    );
  }
  
  if (error || !warrantyData) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center px-4">
        <div className="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center">
          <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span className="text-3xl">❌</span>
          </div>
          <h1 className="text-2xl font-bold text-gray-900 mb-2">
            Ошибка
          </h1>
          <p className="text-gray-600 mb-6">{error || 'Не удалось загрузить данные о гарантии'}</p>
          <button
            onClick={() => router.push('/garantia')}
            className="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-all"
          >
            Вернуться к форме
          </button>
        </div>
      </div>
    );
  }
  
  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center px-4 py-12">
      <div className="max-w-2xl w-full bg-white rounded-2xl shadow-xl p-8 sm:p-12">
        <div className="text-center mb-8">
          <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
            <CheckCircle className="w-12 h-12 text-green-600" />
          </div>
          
          <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
            Спасибо!
          </h1>
          <p className="text-xl text-gray-700 mb-2">
            Гарантийный талон активирован
          </p>
          <p className="text-gray-600">
            Ваша обратная связь очень важна для нас
          </p>
        </div>
        
        <div className="bg-gradient-to-r from-primary-50 to-purple-50 rounded-xl p-6 mb-8 border border-primary-200">
          <div className="space-y-3">
            <div className="flex justify-between items-center">
              <span className="text-sm font-medium text-gray-700">Номер гарантийного талона:</span>
              <span className="text-lg font-bold text-primary-700">{warrantyData.warranty_id}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-sm font-medium text-gray-700">Дата активации:</span>
              <span className="font-medium text-gray-900">
                {new Date(warrantyData.activated_at).toLocaleDateString('ru-RU')}
              </span>
            </div>
            {warrantyData.discounts_reserved_until && (
              <div className="flex justify-between items-center">
                <span className="text-sm font-medium text-gray-700">Скидки действуют до:</span>
                <span className="font-medium text-green-700">{warrantyData.discounts_reserved_until}</span>
              </div>
            )}
          </div>
        </div>
        
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
          <p className="text-sm text-blue-800">
            📧 Подтверждение активации отправлено на ваш email. 
            Гарантийный талон также доступен в личном кабинете.
          </p>
        </div>
        
        <div className="flex flex-col sm:flex-row gap-4">
          <button
            onClick={() => router.push('/')}
            className="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-all shadow-lg hover:shadow-xl"
          >
            <Home className="w-5 h-5" />
            На главную
          </button>
          <button
            onClick={() => window.print()}
            className="flex-1 flex items-center justify-center gap-2 px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-all"
          >
            <FileText className="w-5 h-5" />
            Распечатать
          </button>
        </div>
        
        <div className="mt-8 text-center text-sm text-gray-600">
          <p>
            Если у вас возникли вопросы, свяжитесь с нами: <br />
            <a href="tel:+78001234567" className="text-primary-600 hover:underline font-medium">
              8 (800) 123-45-67
            </a>
          </p>
        </div>
      </div>
    </div>
  );
}
