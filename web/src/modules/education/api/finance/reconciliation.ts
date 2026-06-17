import type { MineResult } from '../foundation/types.ts'
import type { FinancePage, FinanceScopedParams } from './types.ts'
import { financeGetOptions, financeRequestOptions } from './types.ts'

export interface ReconciliationBatchRecord {
  id: number
  batch_no: string
  channel_code: string
  business_date: string
  status: string
  total_count: number
  matched_count: number
  unmatched_count: number
}

export interface ReconciliationItemRecord {
  id: number
  batch_id: number
  channel_trade_no: string
  amount_cents: number
  match_status: string
  difference_cents: number
}

export function pageReconciliationBatches(params: FinanceScopedParams): Promise<MineResult<FinancePage<ReconciliationBatchRecord>>> {
  return useHttp().get('/admin/education/finance/reconciliation-batches/page', financeGetOptions(params))
}

export function importReconciliationBatch(payload: FinanceScopedParams & { channel_code: string, business_date: string, rows?: Array<Record<string, unknown>>, file_url?: string }): Promise<MineResult<{ batch_id: number, total_count: number, matched_count: number, unmatched_count: number }>> {
  return useHttp().post('/admin/education/finance/reconciliation-batches', payload, financeRequestOptions(payload))
}

export function matchReconciliationBatch(id: number, payload: FinanceScopedParams = {}): Promise<MineResult<ReconciliationBatchRecord>> {
  return useHttp().post(`/admin/education/finance/reconciliation-batches/${id}/match`, payload, financeRequestOptions(payload))
}

export function pageReconciliationItems(params: FinanceScopedParams & { batch_id?: number }): Promise<MineResult<FinancePage<ReconciliationItemRecord>>> {
  return useHttp().get('/admin/education/finance/reconciliation-items/page', financeGetOptions(params))
}
