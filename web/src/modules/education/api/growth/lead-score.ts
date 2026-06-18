import type { MineResult } from '../foundation/types.ts'
import type { GrowthScopedParams } from './types.ts'
import { growthRequestOptions } from './types.ts'

export interface LeadScoreResult {
  lead_id: number
  score: number
  score_level: string
}

export function recalculateLeadScore(leadId: number, payload: GrowthScopedParams & { reason?: string, score_date?: string }): Promise<MineResult<LeadScoreResult>> {
  return useHttp().post(`/admin/education/growth/leads/${leadId}/score/recalculate`, payload, growthRequestOptions(payload))
}
