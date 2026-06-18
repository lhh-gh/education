import type { MineResult } from '../foundation/types.ts'
import type { GrowthScopedParams } from './types.ts'
import { growthGetOptions } from './types.ts'

export function getTrialConversionLink(leadId: number, params: GrowthScopedParams = {}): Promise<MineResult<{ lead_id: number, conversion_owner: string }>> {
  return useHttp().get(`/admin/education/growth/leads/${leadId}/trial-conversion-link`, growthGetOptions(params))
}
