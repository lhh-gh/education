import type { MinePage, PageParams } from '../foundation/types.ts'

export interface PayrollScopedParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  teacher_id?: number
  salary_month?: string
  metric_month?: string
  status?: string
  keyword?: string
}

export interface PayrollPage<T> extends MinePage<T> {}

export function payrollRequestOptions(input: { tenant_id?: number, campus_id?: number } = {}): { headers?: Record<string, string> } {
  const headers: Record<string, string> = {}
  if (input.tenant_id && input.tenant_id > 0) {
    headers['X-Tenant-Id'] = String(input.tenant_id)
  }
  if (input.campus_id && input.campus_id > 0) {
    headers['X-Campus-Id'] = String(input.campus_id)
  }

  return Object.keys(headers).length > 0 ? { headers } : {}
}

export function payrollGetOptions<T extends PayrollScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return { params, ...payrollRequestOptions(params) }
}
