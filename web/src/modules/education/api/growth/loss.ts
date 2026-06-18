import type { MineResult } from '../foundation/types.ts'
import type { GrowthScopedParams } from './types.ts'
import { growthRequestOptions } from './types.ts'

export interface LossReasonPayload extends GrowthScopedParams {
  reason_code: string
  reason_name: string
  category: string
}

export interface LeadLossPayload extends GrowthScopedParams {
  loss_reason_id: number
  detail: string
}

export function saveLossReason(payload: LossReasonPayload): Promise<MineResult<{ reason_id: number, status: string }>> {
  return useHttp().post('/admin/education/growth/loss-reasons', payload, growthRequestOptions(payload))
}

export function createLeadLossRecord(leadId: number, payload: LeadLossPayload): Promise<MineResult<{ lead_id: number, status: string }>> {
  return useHttp().post(`/admin/education/growth/leads/${leadId}/loss-records`, payload, growthRequestOptions(payload))
}
