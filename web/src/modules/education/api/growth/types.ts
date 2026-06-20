import type { MinePage, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

export interface GrowthScopedParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  owner_user_id?: number
  consultant_user_id?: number
  source_id?: number
  score_level?: string
  start_date?: string
  end_date?: string
  metric_date?: string
}

export interface GrowthPage<T> extends MinePage<T> {}

export function growthRequestOptions(input: { tenant_id?: number, campus_id?: number } = {}): { headers?: Record<string, string> } {
  return educationScopeRequestOptions(input)
}

export function growthGetOptions<T extends GrowthScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return educationScopeGetOptions(params)
}

export type GrowthTagType = '' | 'success' | 'warning' | 'danger' | 'info'
