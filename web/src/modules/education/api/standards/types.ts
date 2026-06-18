import type { MinePage, PageParams } from '../foundation/types.ts'

export interface StandardsScopedParams extends Partial<PageParams> {
  tenant_id?: number
  campus_id?: number
  course_id?: number
  status?: string
  start_date?: string
  end_date?: string
}

export interface StandardsPage<T> extends MinePage<T> {}

export function standardsRequestOptions(input: { tenant_id?: number, campus_id?: number } = {}): { headers?: Record<string, string> } {
  const headers: Record<string, string> = {}
  if (input.tenant_id && input.tenant_id > 0) {
    headers['X-Tenant-Id'] = String(input.tenant_id)
  }
  if (input.campus_id && input.campus_id > 0) {
    headers['X-Campus-Id'] = String(input.campus_id)
  }

  return Object.keys(headers).length > 0 ? { headers } : {}
}

export function standardsGetOptions<T extends StandardsScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return { params, ...standardsRequestOptions(params) }
}

export interface StandardVersionRow {
  id: number
  business_type: string
  business_id: number
  version_no: number
  status: 'draft' | 'reviewing' | 'published' | 'withdrawn' | 'archived'
  review_status?: 'pending' | 'approved' | 'rejected'
}
