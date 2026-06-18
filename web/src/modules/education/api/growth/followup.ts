import type { MineResult } from '../foundation/types.ts'
import type { GrowthScopedParams } from './types.ts'
import { growthRequestOptions } from './types.ts'

export interface FollowupStrategyPayload extends GrowthScopedParams {
  strategy_code: string
  strategy_name: string
  lead_stage: string
  score_level: string
  suggestion_template: string
  next_follow_hours: number
}

export function saveFollowupStrategy(payload: FollowupStrategyPayload): Promise<MineResult<{ strategy_id: number, status: string }>> {
  return useHttp().post('/admin/education/growth/followup-strategies', payload, growthRequestOptions(payload))
}
