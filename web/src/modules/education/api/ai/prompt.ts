import type { MineResult } from '../foundation/types.ts'
import type { AiPage, AiScopedParams } from './types.ts'
import { aiGetOptions, aiRequestOptions } from './types.ts'

export interface PromptTemplateRecord {
  id: number
  template_code: string
  feature_code: string
  template_name: string
  version: number
  system_prompt: string
  user_prompt: string
  status: string
  published_at?: string
}

export interface PromptTemplatePayload extends AiScopedParams {
  template_code: string
  feature_code: string
  template_name: string
  version?: number
  system_prompt: string
  user_prompt: string
  status?: string
}

export function pagePromptTemplates(params: AiScopedParams): Promise<MineResult<AiPage<PromptTemplateRecord>>> {
  return useHttp().get('/admin/education/ai/prompt-templates/page', aiGetOptions(params))
}

export function savePromptTemplate(payload: PromptTemplatePayload): Promise<MineResult<{ id: number, status: string }>> {
  return useHttp().post('/admin/education/ai/prompt-templates', payload, aiRequestOptions(payload))
}

export function publishPromptTemplate(templateCode: string, payload: AiScopedParams & { version?: number }): Promise<MineResult<{ id: number, status: string }>> {
  return useHttp().post(`/admin/education/ai/prompt-templates/${templateCode}/publish`, payload, aiRequestOptions(payload))
}
