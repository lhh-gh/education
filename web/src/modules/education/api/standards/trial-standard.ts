import type { MineResult } from '../foundation/types.ts'
import type { StandardsScopedParams } from './types.ts'
import { standardsGetOptions, standardsRequestOptions } from './types.ts'

export interface TrialStandardItemPayload {
  item_name: string
  item_content: string
  score_weight?: number
  sort_order?: number
}

export interface TrialStandardPayload extends StandardsScopedParams {
  course_id: number
  standard_code: string
  standard_name: string
  guardian_visible?: boolean
  items?: TrialStandardItemPayload[]
}

export function pageTrialStandards(params: StandardsScopedParams): Promise<MineResult<any>> {
  return useHttp().get('/admin/education/standards/trial-standards', standardsGetOptions(params))
}

export function saveTrialStandard(payload: TrialStandardPayload): Promise<MineResult<{ trial_standard_id: number }>> {
  return useHttp().post('/admin/education/standards/trial-standards', payload, standardsRequestOptions(payload))
}

export function saveTrialStandardItems(payload: TrialStandardPayload): Promise<MineResult<{ trial_standard_id: number }>> {
  return saveTrialStandard(payload)
}
