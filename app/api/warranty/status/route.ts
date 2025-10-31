import { NextRequest, NextResponse } from 'next/server';

// Force dynamic rendering since we use searchParams
export const dynamic = 'force-dynamic';

// Mock database - would be real in production
const mockWarranties = new Map<string, any>();

export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams;
    const contract = searchParams.get('contract');
    
    if (!contract) {
      return NextResponse.json(
        { error: 'Параметр contract обязателен' },
        { status: 400 }
      );
    }
    
    // In production: query actual database
    const warranty = mockWarranties.get(contract);
    
    if (!warranty) {
      return NextResponse.json({
        activated: false,
      });
    }
    
    return NextResponse.json({
      activated: true,
      warranty_id: warranty.id,
      contract_id: warranty.contract_id,
      activated_at: warranty.activated_at,
      discounts_reserved_until: warranty.discounts_reserved_until,
    });
    
  } catch (error) {
    console.error('Status check error:', error);
    return NextResponse.json(
      { error: 'Внутренняя ошибка сервера' },
      { status: 500 }
    );
  }
}
