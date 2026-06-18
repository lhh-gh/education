import type { MineResult } from '../foundation/types.ts'
import type { AiPage, AiScopedParams } from './types.ts'
import { aiGetOptions, aiRequestOptions } from './types.ts'

export interface RecommendationTaskRecord {
  id: number
  recommendation_type: string
  target_type: string
  target_id?: number
  assignee_user_id?: number
  status: string
  recommendation_json: Record<string, unknown>
  handled_at?: string
}

export function pageRecommendationTasks(params: AiScopedParams): Promise<MineResult<AiPage<RecommendationTaskRecord>>> {
  return useHttp().get('/admin/education/ai/recommendation-tasks/page', aiGetOptions(params))
}

export function markRecommendationHandled(id: number, payload: AiScopedParams = {}): Promise<MineResult<{ recommendation_task_id: number, status: string }>> {
  return useHttp().post(`/admin/education/ai/recommendation-tasks/${id}/handle`, payload, aiRequestOptions(payload))
}
