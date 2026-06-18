import type { MineResult } from '../foundation/types.ts'
import type { FinancePage, FinanceScopedParams } from './types.ts'
import { financeGetOptions, financeRequestOptions } from './types.ts'

export interface FinanceOrderRecord {
  id?: number
  order_id?: number
  tenant_id?: number
  campus_id?: number | null
  order_no: string
  order_type?: string
  student_id: number
  guardian_id?: number | null
  enrollment_id?: number | null
  total_amount_cents: number
  paid_amount_cents?: number
  refund_amount_cents?: number
  status: string
  paid_at?: string | null
  due_at?: string | null
}

export interface FinanceOrderItemPayload {
  item_type: string
  item_name: string
  quantity: string
  unit_amount_cents: number
  source_type?: string
  source_id?: number
}

export interface FinanceOrderCreatePayload extends FinanceScopedParams {
  enrollment_id?: number
  student_id: number
  guardian_id?: number
  order_type?: string
  items: FinanceOrderItemPayload[]
}

export function pageFinanceOrders(params: FinanceScopedParams): Promise<MineResult<FinancePage<FinanceOrderRecord>>> {
  return useHttp().get('/admin/education/finance/orders/page', financeGetOptions(params))
}

export function createOrderFromEnrollment(payload: FinanceOrderCreatePayload): Promise<MineResult<FinanceOrderRecord>> {
  return useHttp().post('/admin/education/finance/orders/from-enrollment', payload, financeRequestOptions(payload))
}

export function createFinanceOrder(payload: FinanceOrderCreatePayload): Promise<MineResult<FinanceOrderRecord>> {
  return useHttp().post('/admin/education/finance/orders', payload, financeRequestOptions(payload))
}

export function cancelFinanceOrder(id: number, payload: FinanceScopedParams = {}): Promise<MineResult<FinanceOrderRecord>> {
  return useHttp().post(`/admin/education/finance/orders/${id}/cancel`, payload, financeRequestOptions(payload))
}

export function getFinanceOrderDetail(id: number, params: FinanceScopedParams = {}): Promise<MineResult<FinanceOrderRecord>> {
  return useHttp().get(`/admin/education/finance/orders/${id}`, financeGetOptions(params))
}
