import type { MineResult } from '../foundation/types.ts'
import type { GrowthScopedParams } from './types.ts'
import { growthGetOptions, growthRequestOptions } from './types.ts'

export interface ChannelRoiRecord {
  source_id: number
  lead_count: number
  converted_count: number
  cost_cents: number
  converted_revenue_cents: number
  roi: string | null
}

export interface ChannelCostPayload extends GrowthScopedParams {
  source_id: number
  cost_date: string
  amount_cents: number
}

export function getChannelRoi(params: GrowthScopedParams): Promise<MineResult<{ list: ChannelRoiRecord[] }>> {
  return useHttp().get('/admin/education/growth/channel-roi', growthGetOptions(params))
}

export function saveChannelCost(payload: ChannelCostPayload): Promise<MineResult<{ channel_cost_id: number, status: string }>> {
  return useHttp().post('/admin/education/growth/channel-costs', payload, growthRequestOptions(payload))
}
