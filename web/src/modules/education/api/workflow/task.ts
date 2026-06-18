import type { MineResult } from '../foundation/types.ts'
import type { WorkflowPage, WorkflowScopedParams } from './types.ts'
import { workflowGetOptions, workflowRequestOptions } from './types.ts'

export type WorkflowTaskStatus = 'pending' | 'processing' | 'completed' | 'cancelled' | 'overdue'

export interface WorkflowTaskRecord {
  id: number
  task_no: string
  task_type: string
  title: string
  priority: string
  status: WorkflowTaskStatus
  due_at?: string
  assignee_user_ids?: number[]
}

export function pageWorkflowTasks(params: WorkflowScopedParams): Promise<MineResult<WorkflowPage<WorkflowTaskRecord>>> {
  return useHttp().get('/admin/education/workflow/tasks/page', workflowGetOptions(params))
}

export function getWorkflowTaskDetail(id: number, params: WorkflowScopedParams = {}): Promise<MineResult<WorkflowTaskRecord>> {
  return useHttp().get(`/admin/education/workflow/tasks/${id}`, workflowGetOptions(params))
}

export function completeWorkflowTask(id: number, payload: WorkflowScopedParams & { result: string, content: string }): Promise<MineResult<{ task_id: number, status: string }>> {
  return useHttp().post(`/admin/education/workflow/tasks/${id}/complete`, payload, workflowRequestOptions(payload))
}

export function addWorkflowTaskComment(id: number, payload: WorkflowScopedParams & { content: string }): Promise<MineResult<{ task_id: number, commented: boolean }>> {
  return useHttp().post(`/admin/education/workflow/tasks/${id}/comments`, payload, workflowRequestOptions(payload))
}

export function uploadTaskAttachment(id: number, payload: WorkflowScopedParams & { file_name: string, file_url: string, file_size?: number }): Promise<MineResult<{ task_id: number }>> {
  return useHttp().post(`/admin/education/workflow/tasks/${id}/attachments`, payload, workflowRequestOptions(payload))
}
