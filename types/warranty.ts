export interface ExtraWork {
  title: string;
  price?: number;
}

export interface WarrantyFormData {
  phone_or_contract: string;
  has_extra_work: boolean;
  extra_work: ExtraWork[];
  sales_rate?: number;
  delivery_rate?: number;
  installation_rate?: number;
  discounts: string[];
}

export interface WarrantyActivationResponse {
  activated: boolean;
  warranty_id: string;
  contract_id: string;
  discounts_reserved_until: string;
}

export interface WarrantyStatusResponse {
  activated: boolean;
  warranty_id?: string;
  activated_at?: string;
  contract_id?: string;
}

export type FormStep = 1 | 2 | 3 | 4 | 5 | 6;

export const DISCOUNT_OPTIONS = [
  { id: 'glue_10', title: 'Клей для ламината', discount: 10 },
  { id: 'molding_5', title: 'Плинтус', discount: 5 },
  { id: 'underlay_5', title: 'Подложка', discount: 5 },
  { id: 'primer_10', title: 'Грунтовка', discount: 10 },
  { id: 'installation_30', title: 'Укладка', discount: 30 },
  { id: 'none', title: 'Ничего не нужно', discount: 0 },
] as const;

export const RATING_LABELS = [
  'Ужасно',
  'Плохо',
  'Не понравилось',
  'Хорошо',
  'Отлично',
] as const;
