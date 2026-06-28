import type { MineResult } from '../foundation/types.ts'
import type { StandardsScopedParams } from './types.ts'
import { standardsGetOptions, standardsRequestOptions } from './types.ts'

export interface CourseFeedbackPayload extends StandardsScopedParams {
  course_id: number
  standard_version_id?: number
  feedback_type: string
  score?: number
  content: string
  source_type?: string
  source_id?: number
}

export function pageCourseFeedbackRecords(params: StandardsScopedParams): Promise<MineResult<any>> {
  return useHttp().get('/admin/education/standards/feedback-records', standardsGetOptions(params))
}

export function saveCourseFeedbackRecord(payload: CourseFeedbackPayload): Promise<MineResult<{ feedback_id: number }>> {
  return useHttp().post('/admin/education/standards/feedback-records', payload, standardsRequestOptions(payload))
}

export function getCourseQualityMetrics(params: StandardsScopedParams): Promise<MineResult<any>> {
  return useHttp().get('/admin/education/standards/quality-metrics', standardsGetOptions(params))
}
