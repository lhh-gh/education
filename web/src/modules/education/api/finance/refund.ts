import type { MineResult } from '../foundation/types.ts'
import type { FinancePage, FinanceScopedParams } from './types.ts'
import { financeGetOptions, financeRequestOptions } from './types.ts'

export interface RefundRequestRecord {
  id: number
  order_id: number
  payment_record_id?: number | null
  refund_no: string
  refund_amount_cents: number
  reason: string
  status: string
  reviewed_at?: string | null
}

export interface RefundRequestPayload extends FinanceScopedParams {
  order_id: number
  payment_record_id?: number
  refund_amount_cents: number
  reason: string
}

export function pageRefundRequests(params: FinanceScopedParams): Promise<MineResult<FinancePage<RefundRequestRecord>>> {
  return useHttp().get('/admin/education/finance/refund-requests/page', financeGetOptions(params))
}

export function createRefundRequest(payload: RefundRequestPayload): Promise<MineResult<{ refund_request_id: number, status: string }>> {
  return useHttp().post('/admin/education/finance/refund-requests', payload, financeRequestOptions(payload))
}

export function approveRefundRequest(id: number, payload: FinanceScopedParams & { review_note?: string }): Promise<MineResult<{ refund_request_id: number, status: string }>> {
  return useHttp().post(`/admin/education/finance/refund-requests/${id}/approve`, payload, financeRequestOptions(payload))
}

export function rejectRefundRequest(id: number, payload: FinanceScopedParams & { review_note?: string }): Promise<MineResult<{ refund_request_id: number, status: string }>> {
  return useHttp().post(`/admin/education/finance/refund-requests/${id}/reject`, payload, financeRequestOptions(payload))
}
