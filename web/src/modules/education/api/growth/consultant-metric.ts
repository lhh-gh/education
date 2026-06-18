import type { MineResult } from '../foundation/types.ts'
import type { GrowthScopedParams } from './types.ts'
import { growthGetOptions } from './types.ts'

export interface ConsultantMetricRecord {
  consultant_user_id: number
  assigned_leads_count: number
  follow_count: number
  trial_count: number
  converted_count: number
  lost_count: number
}

export function getConsultantMetrics(params: GrowthScopedParams): Promise<MineResult<{ list: ConsultantMetricRecord[] }>> {
  return useHttp().get('/admin/education/growth/consultant-metrics', growthGetOptions(params))
}
