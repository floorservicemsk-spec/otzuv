import { z } from 'zod';

// Phone validation for Russian format +7 (XXX) XXX-XX-XX
const phoneRegex = /^\+7\s?\(\d{3}\)\s?\d{3}-\d{2}-\d{2}$/;

// Contract validation: IL-, D- prefixes or numeric, 6-16 chars
const contractRegex = /^(IL-|D-)?.{6,16}$/;

export const warrantySchema = z.object({
  phone_or_contract: z.string()
    .min(1, 'Это поле обязательно')
    .refine((val) => {
      const cleanVal = val.replace(/\s/g, '');
      return phoneRegex.test(val) || contractRegex.test(cleanVal);
    }, 'Введите корректный телефон или номер договора'),
  
  has_extra_work: z.boolean().default(false),
  
  extra_work: z.array(z.object({
    title: z.string().min(1, 'Введите описание работ'),
    price: z.number().optional(),
  })).default([]),
  
  sales_rate: z.number().min(1).max(5).optional(),
  delivery_rate: z.number().min(1).max(5).optional(),
  installation_rate: z.number().min(1).max(5).optional(),
  
  discounts: z.array(z.string()).default([]),
});

export type WarrantyFormSchema = z.infer<typeof warrantySchema>;

export const formatPhone = (value: string): string => {
  const cleaned = value.replace(/\D/g, '');
  
  if (cleaned.length === 0) return '';
  if (cleaned.length <= 1) return `+${cleaned}`;
  if (cleaned.length <= 4) return `+${cleaned.slice(0, 1)} (${cleaned.slice(1)}`;
  if (cleaned.length <= 7) return `+${cleaned.slice(0, 1)} (${cleaned.slice(1, 4)}) ${cleaned.slice(4)}`;
  if (cleaned.length <= 9) return `+${cleaned.slice(0, 1)} (${cleaned.slice(1, 4)}) ${cleaned.slice(4, 7)}-${cleaned.slice(7)}`;
  return `+${cleaned.slice(0, 1)} (${cleaned.slice(1, 4)}) ${cleaned.slice(4, 7)}-${cleaned.slice(7, 9)}-${cleaned.slice(9, 11)}`;
};
