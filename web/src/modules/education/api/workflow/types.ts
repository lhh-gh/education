import type { MinePage, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

export interface WorkflowScopedParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  status?: string
  start_date?: string
  end_date?: string
  task_type?: string
}

export interface WorkflowPage<T> extends MinePage<T> {}

export function workflowRequestOptions(input: { tenant_id?: number, campus_id?: number } = {}): { headers?: Record<string, string> } {
  return educationScopeRequestOptions(input)
}

export function workflowGetOptions<T extends WorkflowScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return educationScopeGetOptions(params)
}

export type WorkflowTagType = '' | 'success' | 'warning' | 'danger' | 'info'
