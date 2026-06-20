import type { MinePage, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

export interface FamilyScopedParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  student_id?: number
  teacher_id?: number
  status?: string
  start_date?: string
  end_date?: string
}

export interface FamilyPage<T> extends MinePage<T> {}

export function familyRequestOptions(input: { tenant_id?: number, campus_id?: number } = {}): { headers?: Record<string, string> } {
  return educationScopeRequestOptions(input)
}

export function familyGetOptions<T extends FamilyScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return educationScopeGetOptions(params)
}

export type FamilyTagType = '' | 'success' | 'warning' | 'danger' | 'info'
