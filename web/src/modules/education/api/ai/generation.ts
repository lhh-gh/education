import type { MineResult } from '../foundation/types.ts'
import type { AiPage, AiScopedParams } from './types.ts'
import { aiGetOptions, aiRequestOptions } from './types.ts'

export type GenerationStatus = 'pending' | 'queued' | 'running' | 'succeeded' | 'failed' | 'blocked'

export interface GenerationTaskRecord {
  id: number
  task_no: string
  feature_code: string
  business_type: string
  business_id?: number
  status: GenerationStatus
  requester_user_id: number
  queued_at?: string
  finished_at?: string
  error_message?: string
}

export interface GenerationResultRecord {
  id: number
  generation_task_id: number
  result_text: string
  safety_status: string
  review_status: string
  visible_to_guardian: boolean
}

export interface GenerationTaskPayload extends AiScopedParams {
  feature_code: string
  business_type: string
  business_id?: number
  prompt_variables?: Record<string, unknown>
}

export function createGenerationTask(payload: GenerationTaskPayload): Promise<MineResult<{ task_id: number, status: GenerationStatus }>> {
  return useHttp().post('/admin/education/ai/generation-tasks', payload, aiRequestOptions(payload))
}

export function pageGenerationTasks(params: AiScopedParams): Promise<MineResult<AiPage<GenerationTaskRecord>>> {
  return useHttp().get('/admin/education/ai/generation-tasks/page', aiGetOptions(params))
}

export function getGenerationResult(id: number, params: AiScopedParams = {}): Promise<MineResult<GenerationResultRecord>> {
  return useHttp().get(`/admin/education/ai/generation-results/${id}/detail`, aiGetOptions(params))
}

export function pageGenerationResults(params: AiScopedParams): Promise<MineResult<AiPage<GenerationResultRecord>>> {
  return useHttp().get('/admin/education/ai/generation-results/page', aiGetOptions(params))
}

export function approveGenerationResult(id: number, payload: AiScopedParams & { review_note?: string, edited_text?: string } = {}): Promise<MineResult<{ generation_result_id: number, review_status: string }>> {
  return useHttp().post(`/admin/education/ai/generation-results/${id}/approve`, payload, aiRequestOptions(payload))
}
