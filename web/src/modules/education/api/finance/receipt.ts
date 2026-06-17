import type { MineResult } from '../foundation/types.ts'
import type { FinancePage, FinanceScopedParams } from './types.ts'
import { financeGetOptions, financeRequestOptions } from './types.ts'

export interface ReceiptRecord {
  id: number
  receipt_no: string
  order_id: number
  student_id: number
  amount_cents: number
  status: string
  issued_at?: string | null
  pdf_url?: string | null
}

export function pageReceipts(params: FinanceScopedParams): Promise<MineResult<FinancePage<ReceiptRecord>>> {
  return useHttp().get('/admin/education/finance/receipts/page', financeGetOptions(params))
}

export function issueReceipt(payload: FinanceScopedParams & { order_id: number, amount_cents: number, pdf_url?: string }): Promise<MineResult<ReceiptRecord>> {
  return useHttp().post('/admin/education/finance/receipts', payload, financeRequestOptions(payload))
}

export function voidReceipt(id: number, payload: FinanceScopedParams = {}): Promise<MineResult<ReceiptRecord>> {
  return useHttp().post(`/admin/education/finance/receipts/${id}/void`, payload, financeRequestOptions(payload))
}
