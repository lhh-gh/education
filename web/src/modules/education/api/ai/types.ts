import type { MinePage, PageParams } from '../foundation/types.ts'

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
  const headers: Record<string, string> = {}
  if (input.tenant_id && input.tenant_id > 0) {
    headers['X-Tenant-Id'] = String(input.tenant_id)
  }
  if (input.campus_id && input.campus_id > 0) {
    headers['X-Campus-Id'] = String(input.campus_id)
  }

  return Object.keys(headers).length > 0 ? { headers } : {}
}

export function aiGetOptions<T extends AiScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return { params, ...aiRequestOptions(params) }
}

export type AiTagType = '' | 'success' | 'warning' | 'danger' | 'info'
