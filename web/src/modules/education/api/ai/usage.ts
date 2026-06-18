import type { MineResult } from '../foundation/types.ts'
import type { AiPage, AiScopedParams } from './types.ts'
import { aiGetOptions } from './types.ts'

export interface UsageLogRecord {
  id: number
  provider: string
  model_name: string
  total_tokens: number
  cost_cents: number
  usage_date: string
}

export interface UsageSummary {
  total_tokens: number
  cost_cents: number
}

export function getUsageSummary(params: AiScopedParams): Promise<MineResult<UsageSummary>> {
  return useHttp().get('/admin/education/ai/usage/summary', aiGetOptions(params))
}

export function pageUsageLogs(params: AiScopedParams): Promise<MineResult<AiPage<UsageLogRecord>>> {
  return useHttp().get('/admin/education/ai/usage/page', aiGetOptions(params))
}
