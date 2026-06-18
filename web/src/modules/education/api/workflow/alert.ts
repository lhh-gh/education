import type { MineResult } from '../foundation/types.ts'
import type { WorkflowPage, WorkflowScopedParams } from './types.ts'
import { workflowGetOptions, workflowRequestOptions } from './types.ts'

export interface OperationAlertRecord {
  id: number
  alert_no: string
  alert_type: string
  level: string
  status: 'open' | 'converted' | 'ignored' | 'closed'
  title: string
  converted_task_id?: number
}

export function pageOperationAlerts(params: WorkflowScopedParams): Promise<MineResult<WorkflowPage<OperationAlertRecord>>> {
  return useHttp().get('/admin/education/workflow/alerts/page', workflowGetOptions(params))
}

export function convertAlertToTask(id: number, payload: WorkflowScopedParams & { assignee_user_id: number, due_at?: string }): Promise<MineResult<{ alert_id: number, converted_task_id: number, status: string }>> {
  return useHttp().post(`/admin/education/workflow/alerts/${id}/convert-task`, payload, workflowRequestOptions(payload))
}

export function ignoreAlert(id: number, payload: WorkflowScopedParams = {}): Promise<MineResult<{ alert_id: number, status: string }>> {
  return useHttp().post(`/admin/education/workflow/alerts/${id}/ignore`, payload, workflowRequestOptions(payload))
}

export function closeAlert(id: number, payload: WorkflowScopedParams = {}): Promise<MineResult<{ alert_id: number, status: string }>> {
  return useHttp().post(`/admin/education/workflow/alerts/${id}/close`, payload, workflowRequestOptions(payload))
}
