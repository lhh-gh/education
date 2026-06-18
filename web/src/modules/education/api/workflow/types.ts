import type { MinePage, PageParams } from '../foundation/types.ts'

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
  const headers: Record<string, string> = {}
  if (input.tenant_id && input.tenant_id > 0) {
    headers['X-Tenant-Id'] = String(input.tenant_id)
  }
  if (input.campus_id && input.campus_id > 0) {
    headers['X-Campus-Id'] = String(input.campus_id)
  }

  return Object.keys(headers).length > 0 ? { headers } : {}
}

export function workflowGetOptions<T extends WorkflowScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return { params, ...workflowRequestOptions(params) }
}

export type WorkflowTagType = '' | 'success' | 'warning' | 'danger' | 'info'
