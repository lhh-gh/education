import type { MinePage, PageParams } from '../foundation/types.ts'

export interface FinanceScopedParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  student_id?: number
  status?: string
  keyword?: string
  start_at?: string
  end_at?: string
}

export interface FinancePage<T> extends MinePage<T> {}

export function financeRequestOptions(input: { tenant_id?: number, campus_id?: number } = {}): { headers?: Record<string, string> } {
  const headers: Record<string, string> = {}
  if (input.tenant_id && input.tenant_id > 0) {
    headers['X-Tenant-Id'] = String(input.tenant_id)
  }
  if (input.campus_id && input.campus_id > 0) {
    headers['X-Campus-Id'] = String(input.campus_id)
  }

  return Object.keys(headers).length > 0 ? { headers } : {}
}

export function financeGetOptions<T extends FinanceScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return { params, ...financeRequestOptions(params) }
}
