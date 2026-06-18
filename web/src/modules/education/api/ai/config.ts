import type { MineResult } from '../foundation/types.ts'
import type { AiPage, AiScopedParams } from './types.ts'
import { aiGetOptions, aiRequestOptions } from './types.ts'

export interface AiModelConfigRecord {
  id: number
  config_code: string
  provider: string
  model_name: string
  status: string
  api_key_visible?: false
  daily_token_limit?: number
}

export interface AiModelConfigPayload extends AiScopedParams {
  config_code: string
  provider: string
  model_name: string
  api_key?: string
  base_url?: string
  status?: string
  default_temperature?: number
  daily_token_limit?: number
}

export interface AiFeatureSettingPayload extends AiScopedParams {
  feature_code: string
  feature_name: string
  model_config_id: number
  enabled?: boolean
  review_required?: boolean
  safety_level?: string
  config_json?: Record<string, unknown>
}

export function pageModelConfigs(params: AiScopedParams): Promise<MineResult<AiPage<AiModelConfigRecord>>> {
  return useHttp().get('/admin/education/ai/model-configs/page', aiGetOptions(params))
}

export function saveModelConfig(payload: AiModelConfigPayload): Promise<MineResult<AiModelConfigRecord>> {
  return useHttp().post('/admin/education/ai/model-configs', payload, aiRequestOptions(payload))
}

export function saveFeatureSetting(payload: AiFeatureSettingPayload): Promise<MineResult<{ id: number, feature_code: string }>> {
  return useHttp().post('/admin/education/ai/feature-settings', payload, aiRequestOptions(payload))
}
