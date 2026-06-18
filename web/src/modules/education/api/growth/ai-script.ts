import type { MineResult } from '../foundation/types.ts'
import type { GrowthScopedParams } from './types.ts'
import { growthRequestOptions } from './types.ts'

export interface AiTalkScriptGeneratePayload extends GrowthScopedParams {
  lead_id: number
  script_type: string
  goal: string
  generated_text?: string
}

export interface AiTalkScriptConfirmPayload extends GrowthScopedParams {
  edited_script: string
}

export interface AiTalkScriptResult {
  ai_talk_script_id: number
  status: string
}

export function generateAiTalkScript(payload: AiTalkScriptGeneratePayload): Promise<MineResult<AiTalkScriptResult>> {
  return useHttp().post('/admin/education/growth/ai-talk-scripts/generate', payload, growthRequestOptions(payload))
}

export function confirmAiTalkScript(id: number, payload: AiTalkScriptConfirmPayload): Promise<MineResult<AiTalkScriptResult>> {
  return useHttp().post(`/admin/education/growth/ai-talk-scripts/${id}/confirm`, payload, growthRequestOptions(payload))
}
