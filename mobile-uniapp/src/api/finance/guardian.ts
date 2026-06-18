import type { OperationScopedParams, PageResult } from '../operations/shared'
import { requestOperation, selectedStudentId } from '../operations/shared'

export { MobileApiError } from '../operations/shared'

export type GuardianFinanceOrderStatus =
  | 'pending'
  | 'paying'
  | 'paid'
  | 'partial_refunded'
  | 'refunded'
  | 'cancelled'
  | 'closed'

export type GuardianReceiptStatus = 'issued' | 'voided'
export type GuardianRefundStatus = 'pending' | 'approved' | 'rejected' | 'processing' | 'refunded' | 'failed' | 'cancelled'

export interface GuardianFinanceParams extends OperationScopedParams {
  status?: string
}

export interface GuardianFinanceOrder {
  id: number
  order_no: string
  order_type: string
  student_id: number
  total_amount_cents: number
  paid_amount_cents: number
  refund_amount_cents: number
  discount_amount_cents?: number
  status: GuardianFinanceOrderStatus | string
  due_at?: string | null
  paid_at?: string | null
  remark?: string | null
}

export interface GuardianFinanceReceipt {
  id: number
  receipt_no: string
  order_id: number
  student_id: number
  amount_cents: number
  status: GuardianReceiptStatus | string
  issued_at?: string | null
  pdf_url?: string | null
}

export interface GuardianFinanceRefund {
  id: number
  order_id: number
  payment_record_id?: number | null
  refund_no: string
  refund_amount_cents: number
  reason: string
  status: GuardianRefundStatus | string
  reviewed_at?: string | null
  review_note?: string | null
}

export function getGuardianFinanceOrders(params?: GuardianFinanceParams): Promise<PageResult<GuardianFinanceOrder>> {
  return requestGuardianFinanceList('/mobile/education/finance/guardian/orders', params)
}

export async function getGuardianFinanceOrder(
  orderId: number,
  params?: GuardianFinanceParams,
): Promise<GuardianFinanceOrder> {
  const result = await getGuardianFinanceOrders({ ...params, page: 1, pageSize: 100 })
  const order = result.list.find(row => row.id === orderId)
  if (order) {
    return order
  }

  const error = new Error('Finance order not found') as Error & { code?: number, data?: object }
  error.code = 404
  error.data = { order_id: orderId }
  throw error
}

export function getGuardianFinanceReceipts(params?: GuardianFinanceParams): Promise<PageResult<GuardianFinanceReceipt>> {
  return requestGuardianFinanceList('/mobile/education/finance/guardian/receipts', params)
}

export function getGuardianFinanceRefunds(params?: GuardianFinanceParams): Promise<PageResult<GuardianFinanceRefund>> {
  return requestGuardianFinanceList('/mobile/education/finance/guardian/refunds', params)
}

function requestGuardianFinanceList<T>(url: string, params?: GuardianFinanceParams): Promise<PageResult<T>> {
  return requestOperation(url, 'GET', { ...params, student_id: selectedStudentId(params) })
}
