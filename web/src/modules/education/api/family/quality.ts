import type { MineResult } from '../foundation/types.ts'
import type { FamilyScopedParams } from './types.ts'
import { familyGetOptions } from './types.ts'

export interface ServiceQualityMetric {
  id: number
  metric_date: string
  teacher_id?: number
  student_id?: number
  comment_count: number
  homework_review_count: number
  report_count: number
  message_response_minutes?: number
}

export function getServiceQualityMetrics(params: FamilyScopedParams): Promise<MineResult<ServiceQualityMetric[]>> {
  return useHttp().get('/admin/education/family/service-quality', familyGetOptions(params))
}

export function getServiceQualityDashboard(params: FamilyScopedParams): Promise<MineResult<ServiceQualityMetric[]>> {
  return useHttp().get('/admin/education/family/service-quality', familyGetOptions(params))
}
