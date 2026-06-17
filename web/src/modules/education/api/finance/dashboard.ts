import type { MineResult } from '../foundation/types.ts'
import type { FinanceScopedParams } from './types.ts'
import { financeGetOptions } from './types.ts'

export interface FinanceOverview {
  order_count: number
  paid_amount_cents: number
  refund_amount_cents: number
  payment_count: number
}

export function getFinanceOverview(params: FinanceScopedParams): Promise<MineResult<FinanceOverview>> {
  return useHttp().get('/admin/education/finance/dashboard/summary', financeGetOptions(params))
}

export function getPaymentTrend(params: FinanceScopedParams): Promise<MineResult<{ list: Array<Record<string, unknown>> }>> {
  return useHttp().get('/admin/education/finance/dashboard/payment-trend', financeGetOptions(params))
}

export function getRefundSummary(params: FinanceScopedParams): Promise<MineResult<Record<string, unknown>>> {
  return useHttp().get('/admin/education/finance/dashboard/refund-summary', financeGetOptions(params))
}

export function getReconciliationSummary(params: FinanceScopedParams): Promise<MineResult<Record<string, unknown>>> {
  return useHttp().get('/admin/education/finance/dashboard/reconciliation-summary', financeGetOptions(params))
}
