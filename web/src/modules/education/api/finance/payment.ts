import type { MineResult } from '../foundation/types.ts'
import type { FinancePage, FinanceScopedParams } from './types.ts'
import { financeGetOptions, financeRequestOptions } from './types.ts'

export interface PaymentRecord {
  id: number
  order_id: number
  payment_no: string
  channel_code: string
  channel_trade_no?: string | null
  amount_cents: number
  status: string
  paid_at?: string | null
}

export interface PaymentChannelRecord {
  id: number
  channel_code: string
  channel_name: string
  channel_type: string
  status: string
  sort_order?: number
}

export interface OfflinePaymentPayload extends FinanceScopedParams {
  order_id: number
  channel_code: string
  payment_no?: string
  amount_cents: number
  payer_name?: string
  remark?: string
}

export function pagePaymentRecords(params: FinanceScopedParams): Promise<MineResult<FinancePage<PaymentRecord>>> {
  return useHttp().get('/admin/education/finance/payment-records/page', financeGetOptions(params))
}

export function confirmOfflinePayment(payload: OfflinePaymentPayload): Promise<MineResult<{ payment_record_id: number, order_status: string }>> {
  return useHttp().post('/admin/education/finance/offline-payments', payload, financeRequestOptions(payload))
}

export function pagePaymentChannels(params: FinanceScopedParams): Promise<MineResult<PaymentChannelRecord[]>> {
  return useHttp().get('/admin/education/finance/payment-channels', financeGetOptions(params))
}

export function savePaymentChannel(payload: Partial<PaymentChannelRecord> & FinanceScopedParams): Promise<MineResult<PaymentChannelRecord>> {
  return useHttp().post('/admin/education/finance/payment-channels', payload, financeRequestOptions(payload))
}
