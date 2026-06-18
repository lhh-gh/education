import type { MinePage, PageParams } from '../foundation/types.ts'

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
  const headers: Record<string, string> = {}
  if (input.tenant_id && input.tenant_id > 0) {
    headers['X-Tenant-Id'] = String(input.tenant_id)
  }
  if (input.campus_id && input.campus_id > 0) {
    headers['X-Campus-Id'] = String(input.campus_id)
  }

  return Object.keys(headers).length > 0 ? { headers } : {}
}

export function growthGetOptions<T extends GrowthScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return { params, ...growthRequestOptions(params) }
}

export type GrowthTagType = '' | 'success' | 'warning' | 'danger' | 'info'
