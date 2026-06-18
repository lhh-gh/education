import type { MineResult } from '../foundation/types.ts'
import type { GrowthScopedParams } from './types.ts'
import { growthGetOptions } from './types.ts'

export interface GrowthHotLead {
  id?: number
  lead_id: number
  score: number
  score_level: string
  summary?: string
}

export interface GrowthSuggestion {
  id: number
  lead_id: number
  suggestion_text: string
  status: 'pending' | 'accepted' | 'ignored'
  due_at?: string
}

export interface GrowthWorkbenchResult {
  hot_leads: GrowthHotLead[]
  suggestions: GrowthSuggestion[]
}

export function getGrowthWorkbench(params: GrowthScopedParams): Promise<MineResult<GrowthWorkbenchResult>> {
  return useHttp().get('/admin/education/growth/workbench', growthGetOptions(params))
}
