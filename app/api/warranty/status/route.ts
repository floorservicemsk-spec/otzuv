import { NextRequest, NextResponse } from 'next/server'

// Симуляция проверки статуса гарантии
async function getWarrantyStatus(
  contract: string
): Promise<{ activated: boolean; warranty_id?: string; activated_at?: string }> {
  // TODO: Реальная проверка в БД
  return {
    activated: false,
  }
}

export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams
    const contract = searchParams.get('contract')

    if (!contract) {
      return NextResponse.json(
        { error: 'Параметр contract обязателен' },
        { status: 400 }
      )
    }

    const status = await getWarrantyStatus(contract)

    return NextResponse.json(status)
  } catch (error: any) {
    console.error('Warranty status error:', error)
    return NextResponse.json(
      {
        error: error.message || 'Внутренняя ошибка сервера',
      },
      { status: 500 }
    )
  }
}
