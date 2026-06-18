import type { MineResult } from '../foundation/types.ts'
import type { GrowthScopedParams } from './types.ts'
import { growthRequestOptions } from './types.ts'

export interface GrowthCampaignPayload extends GrowthScopedParams {
  campaign_code: string
  campaign_name: string
  channel_type: string
  budget_cents: number
}

export function saveGrowthCampaign(payload: GrowthCampaignPayload): Promise<MineResult<{ campaign_id: number, status: string }>> {
  return useHttp().post('/admin/education/growth/campaigns', payload, growthRequestOptions(payload))
}
