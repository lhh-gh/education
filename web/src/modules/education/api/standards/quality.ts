import type { MineResult } from '../foundation/types.ts'
import type { StandardsScopedParams } from './types.ts'
import { standardsGetOptions } from './types.ts'

export function pageCourseFeedbackRecords(params: StandardsScopedParams): Promise<MineResult<any>> {
  return useHttp().get('/admin/education/standards/feedback-records', standardsGetOptions(params))
}

export function getCourseQualityMetrics(params: StandardsScopedParams): Promise<MineResult<any>> {
  return useHttp().get('/admin/education/standards/quality-metrics', standardsGetOptions(params))
}
