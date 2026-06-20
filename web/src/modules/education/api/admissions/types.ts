import type { MinePage, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

export interface AdmissionScopedParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  keyword?: string
}

export interface AdmissionPage<T> extends MinePage<T> {}

export function admissionRequestOptions(input: { tenant_id?: number, campus_id?: number } = {}): { headers?: Record<string, string> } {
  return educationScopeRequestOptions(input)
}

export function admissionGetOptions<T extends AdmissionScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return educationScopeGetOptions(params)
}
