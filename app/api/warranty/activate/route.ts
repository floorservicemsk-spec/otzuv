import { NextRequest, NextResponse } from 'next/server';
import { warrantySchema } from '@/lib/validation';

// Mock database - In production, this would be a real database
const mockDatabase = {
  warranties: [] as any[],
  contracts: new Map([
    ['IL-123456', { id: 'IL-123456', phone: '+7 (999) 123-45-67', has_installation: true }],
    ['D-789012', { id: 'D-789012', phone: '+7 (999) 987-65-43', has_installation: false }],
  ]),
  activatedWarranties: new Set<string>(),
};

// Rate limiting - In production, use Redis or similar
const rateLimits = new Map<string, { count: number; resetAt: number }>();

function checkRateLimit(ip: string): boolean {
  const now = Date.now();
  const limit = rateLimits.get(ip);
  
  if (!limit || now > limit.resetAt) {
    rateLimits.set(ip, { count: 1, resetAt: now + 60 * 60 * 1000 }); // 1 hour
    return true;
  }
  
  if (limit.count >= 5) {
    return false;
  }
  
  limit.count++;
  return true;
}

function validateContract(phoneOrContract: string): { valid: boolean; contractId?: string; hasInstallation?: boolean } {
  // Check if it's a phone number
  if (phoneOrContract.startsWith('+7')) {
    // In production, lookup by phone in database
    for (const [id, contract] of mockDatabase.contracts.entries()) {
      if (contract.phone === phoneOrContract) {
        return { valid: true, contractId: id, hasInstallation: contract.has_installation };
      }
    }
    return { valid: false };
  }
  
  // Check if it's a contract ID
  const contract = mockDatabase.contracts.get(phoneOrContract);
  if (contract) {
    return { valid: true, contractId: phoneOrContract, hasInstallation: contract.has_installation };
  }
  
  return { valid: false };
}

export async function POST(request: NextRequest) {
  try {
    // Rate limiting
    const ip = request.headers.get('x-forwarded-for') || request.headers.get('x-real-ip') || 'unknown';
    
    if (!checkRateLimit(ip)) {
      return NextResponse.json(
        { error: 'Превышен лимит запросов. Попробуйте позже.' },
        { status: 429 }
      );
    }
    
    // Parse and validate request body
    const body = await request.json();
    const validationResult = warrantySchema.safeParse(body);
    
    if (!validationResult.success) {
      return NextResponse.json(
        { error: 'Некорректные данные формы', details: validationResult.error.errors },
        { status: 400 }
      );
    }
    
    const data = validationResult.data;
    
    // Validate contract/phone exists
    const contractValidation = validateContract(data.phone_or_contract);
    if (!contractValidation.valid) {
      return NextResponse.json(
        { error: 'Договор или телефон не найден. Проверьте введённые данные.' },
        { status: 404 }
      );
    }
    
    const contractId = contractValidation.contractId!;
    
    // Check if already activated
    if (mockDatabase.activatedWarranties.has(contractId)) {
      return NextResponse.json(
        { error: 'Гарантия для этого договора уже активирована', already_activated: true },
        { status: 409 }
      );
    }
    
    // Generate warranty ID
    const warrantyId = `W-${new Date().getFullYear()}-${String(mockDatabase.warranties.length + 1).padStart(6, '0')}`;
    
    // Calculate discount expiration (14 days)
    const discountsReservedUntil = new Date();
    discountsReservedUntil.setDate(discountsReservedUntil.getDate() + 14);
    
    // Save to "database"
    const warranty = {
      id: warrantyId,
      contract_id: contractId,
      phone_or_contract: data.phone_or_contract,
      activated_at: new Date().toISOString(),
      activated_by_ip: ip,
      has_extra_work: data.has_extra_work,
      extra_work: data.extra_work,
      sales_rate: data.sales_rate,
      delivery_rate: data.delivery_rate,
      installation_rate: data.installation_rate,
      discounts: data.discounts,
      discounts_reserved_until: discountsReservedUntil.toISOString(),
    };
    
    mockDatabase.warranties.push(warranty);
    mockDatabase.activatedWarranties.add(contractId);
    
    // In production: 
    // - Save to actual database
    // - Send SMS/Email confirmation
    // - Update CRM
    // - Log to audit table
    
    console.log('Warranty activated:', warranty);
    
    return NextResponse.json({
      activated: true,
      warranty_id: warrantyId,
      contract_id: contractId,
      discounts_reserved_until: discountsReservedUntil.toISOString(),
    });
    
  } catch (error) {
    console.error('Warranty activation error:', error);
    return NextResponse.json(
      { error: 'Внутренняя ошибка сервера. Попробуйте позже.' },
      { status: 500 }
    );
  }
}
