import type { MinePage, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

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
  return educationScopeRequestOptions(input)
}

export function payrollGetOptions<T extends PayrollScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return educationScopeGetOptions(params)
}
