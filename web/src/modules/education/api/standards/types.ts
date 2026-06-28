import type { MinePage, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

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
  return educationScopeRequestOptions(input)
}

export function standardsGetOptions<T extends StandardsScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return educationScopeGetOptions(params)
}

export interface StandardVersionRow {
  id: number
  business_type: string
  business_id: number
  version_no: number
  status: 'draft' | 'reviewing' | 'published' | 'withdrawn' | 'archived'
  review_status?: 'pending' | 'approved' | 'rejected'
}
