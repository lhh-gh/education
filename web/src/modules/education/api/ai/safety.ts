import type { MineResult } from '../foundation/types.ts'
import type { AiPage, AiScopedParams } from './types.ts'
import { aiGetOptions, aiRequestOptions } from './types.ts'

export interface SafetyEventRecord {
  id: number
  generation_task_id?: number
  risk_level: string
  event_type: string
  summary: string
  handled: boolean
}

export function pageSafetyEvents(params: AiScopedParams): Promise<MineResult<AiPage<SafetyEventRecord>>> {
  return useHttp().get('/admin/education/ai/safety-events/page', aiGetOptions(params))
}

export function markSafetyHandled(id: number, payload: AiScopedParams = {}): Promise<MineResult<{ safety_event_id: number, handled: boolean }>> {
  return useHttp().post(`/admin/education/ai/safety-events/${id}/handle`, payload, aiRequestOptions(payload))
}
