import type { MineResult } from '../foundation/types.ts'
import type { AiPage, AiScopedParams } from './types.ts'
import { aiGetOptions } from './types.ts'

export interface RiskScoreRecord {
  id: number
  student_id: number
  risk_score: number
  risk_level: string
  score_date: string
  summary?: string
}

export function pageRiskScores(params: AiScopedParams): Promise<MineResult<AiPage<RiskScoreRecord>>> {
  return useHttp().get('/admin/education/ai/risk-scores/page', aiGetOptions(params))
}

export function getRiskScoreDetail(id: number, params: AiScopedParams = {}): Promise<MineResult<RiskScoreRecord>> {
  return useHttp().get('/admin/education/ai/risk-scores/page', aiGetOptions({ ...params, id }))
}
