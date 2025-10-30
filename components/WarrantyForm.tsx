'use client';

import React, { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { warrantySchema, type WarrantyFormSchema } from '@/lib/validation';
import { analyticsEvents } from '@/lib/analytics';
import { ArrowLeft, ArrowRight, Loader2 } from 'lucide-react';
import ProgressBar from './ProgressBar';
import Step1Identification from './steps/Step1Identification';
import Step2ExtraWork from './steps/Step2ExtraWork';
import Step3SalesRating from './steps/Step3SalesRating';
import Step4DeliveryRating from './steps/Step4DeliveryRating';
import Step5InstallationRating from './steps/Step5InstallationRating';
import Step6Discounts from './steps/Step6Discounts';
import { useRouter } from 'next/navigation';

export default function WarrantyForm() {
  const router = useRouter();
  const [currentStep, setCurrentStep] = useState(1);
  const [hasInstallation, setHasInstallation] = useState(true); // Mock: would come from API
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const totalSteps = hasInstallation ? 6 : 5;
  
  const {
    register,
    handleSubmit,
    control,
    watch,
    setValue,
    formState: { errors },
    trigger,
  } = useForm<WarrantyFormSchema>({
    resolver: zodResolver(warrantySchema),
    mode: 'onChange',
    defaultValues: {
      phone_or_contract: '',
      has_extra_work: false,
      extra_work: [],
      discounts: [],
    },
  });
  
  const phoneOrContract = watch('phone_or_contract');
  const hasExtraWork = watch('has_extra_work');
  
  useEffect(() => {
    analyticsEvents.warrantyStart();
    
    // Try to restore from localStorage
    const saved = localStorage.getItem('warranty_form_draft');
    if (saved) {
      try {
        const data = JSON.parse(saved);
        Object.keys(data).forEach((key) => {
          setValue(key as any, data[key]);
        });
      } catch (e) {
        console.error('Failed to restore draft', e);
      }
    }
  }, [setValue]);
  
  useEffect(() => {
    // Save draft to localStorage
    const subscription = watch((data) => {
      localStorage.setItem('warranty_form_draft', JSON.stringify(data));
    });
    return () => subscription.unsubscribe();
  }, [watch]);
  
  const canGoNext = async () => {
    if (currentStep === 1) {
      const isValid = await trigger('phone_or_contract');
      return isValid;
    }
    return true;
  };
  
  const handleNext = async () => {
    const canProceed = await canGoNext();
    if (!canProceed) {
      setError('Пожалуйста, заполните обязательные поля');
      return;
    }
    
    setError(null);
    analyticsEvents.stepCompleted(currentStep);
    
    // Skip step 5 if no installation
    if (currentStep === 4 && !hasInstallation) {
      setCurrentStep(6);
    } else {
      setCurrentStep(prev => Math.min(prev + 1, totalSteps));
    }
  };
  
  const handleBack = () => {
    setError(null);
    
    // Skip step 5 if no installation
    if (currentStep === 6 && !hasInstallation) {
      setCurrentStep(4);
    } else {
      setCurrentStep(prev => Math.max(prev - 1, 1));
    }
  };
  
  const onSubmit = async (data: WarrantyFormSchema) => {
    setIsSubmitting(true);
    setError(null);
    
    try {
      analyticsEvents.warrantySubmit(data);
      
      const response = await fetch('/api/warranty/activate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Ошибка активации гарантии');
      }
      
      const result = await response.json();
      analyticsEvents.warrantyActivated(result.warranty_id);
      
      // Clear draft
      localStorage.removeItem('warranty_form_draft');
      
      // Redirect to success page
      router.push(`/garantia/success?warranty_id=${result.warranty_id}`);
    } catch (err) {
      console.error('Submission error:', err);
      setError(err instanceof Error ? err.message : 'Произошла ошибка при отправке формы');
    } finally {
      setIsSubmitting(false);
    }
  };
  
  const renderStep = () => {
    switch (currentStep) {
      case 1:
        return (
          <Step1Identification
            register={register}
            errors={errors}
            value={phoneOrContract}
            onChange={(value) => setValue('phone_or_contract', value)}
          />
        );
      case 2:
        return (
          <Step2ExtraWork
            register={register}
            errors={errors}
            control={control}
            hasExtraWork={hasExtraWork}
            onHasExtraWorkChange={(value) => setValue('has_extra_work', value)}
          />
        );
      case 3:
        return <Step3SalesRating control={control} errors={errors} />;
      case 4:
        return <Step4DeliveryRating control={control} errors={errors} />;
      case 5:
        return (
          <Step5InstallationRating
            control={control}
            errors={errors}
            hasInstallation={hasInstallation}
          />
        );
      case 6:
        return <Step6Discounts control={control} />;
      default:
        return null;
    }
  };
  
  const isLastStep = currentStep === totalSteps;
  
  return (
    <>
      <ProgressBar currentStep={currentStep} totalSteps={totalSteps} />
      
      <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 pt-24 pb-12 px-4">
        <div className="max-w-4xl mx-auto">
          <form onSubmit={handleSubmit(onSubmit)} className="bg-white rounded-2xl shadow-xl p-6 sm:p-8 lg:p-12">
            {renderStep()}
            
            {error && (
              <div className="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg" role="alert">
                <p className="text-sm text-red-800">{error}</p>
              </div>
            )}
            
            <div className="mt-8 flex flex-col sm:flex-row gap-4 justify-between">
              <button
                type="button"
                onClick={handleBack}
                disabled={currentStep === 1}
                className="flex items-center justify-center gap-2 px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
              >
                <ArrowLeft className="w-5 h-5" />
                Назад
              </button>
              
              {!isLastStep ? (
                <button
                  type="button"
                  onClick={handleNext}
                  className="flex items-center justify-center gap-2 px-8 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-all shadow-lg hover:shadow-xl disabled:opacity-50"
                >
                  Далее
                  <ArrowRight className="w-5 h-5" />
                </button>
              ) : (
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="flex items-center justify-center gap-2 px-8 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {isSubmitting ? (
                    <>
                      <Loader2 className="w-5 h-5 animate-spin" />
                      Активация...
                    </>
                  ) : (
                    'Активировать гарантию'
                  )}
                </button>
              )}
            </div>
          </form>
          
          <div className="mt-6 text-center text-sm text-gray-600">
            <label className="inline-flex items-center gap-2">
              <input type="checkbox" required className="rounded" />
              <span>
                Я согласен с{' '}
                <a href="/privacy" className="text-primary-600 hover:underline">
                  политикой конфиденциальности
                </a>
              </span>
            </label>
          </div>
        </div>
      </div>
    </>
  );
}
