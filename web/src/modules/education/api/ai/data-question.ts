import type { MineResult } from '../foundation/types.ts'
import type { AiPage, AiScopedParams } from './types.ts'
import { aiGetOptions, aiRequestOptions } from './types.ts'

export interface DataQuestionLogRecord {
  id: number
  question_text: string
  metric_codes_json: string[]
  answer_text?: string
  status: string
}

export interface MetricCatalogRecord {
  id: number
  metric_code: string
  metric_name: string
  metric_group: string
  status: string
}

export interface DataQuestionPayload extends AiScopedParams {
  question_text: string
  metric_codes: string[]
  date_range?: [string, string]
}

export function askDataQuestion(payload: DataQuestionPayload): Promise<MineResult<{ question_log_id: number, status: string, answer_text: string }>> {
  return useHttp().post('/admin/education/ai/data-questions', payload, aiRequestOptions(payload))
}

export function pageDataQuestionLogs(params: AiScopedParams): Promise<MineResult<AiPage<DataQuestionLogRecord>>> {
  return useHttp().get('/admin/education/ai/data-questions/page', aiGetOptions(params))
}

export function pageMetricCatalogs(params: AiScopedParams): Promise<MineResult<AiPage<MetricCatalogRecord>>> {
  return useHttp().get('/admin/education/ai/metric-catalogs/page', aiGetOptions(params))
}
