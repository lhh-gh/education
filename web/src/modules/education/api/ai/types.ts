import type { MinePage, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

export interface AiScopedParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  status?: string
  start_date?: string
  end_date?: string
  feature_code?: string
  risk_level?: string
}

export interface AiPage<T> extends MinePage<T> {}

export function aiRequestOptions(input: { tenant_id?: number, campus_id?: number } = {}): { headers?: Record<string, string> } {
  return educationScopeRequestOptions(input)
}

export function aiGetOptions<T extends AiScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return educationScopeGetOptions(params)
}

export type AiTagType = '' | 'success' | 'warning' | 'danger' | 'info'
