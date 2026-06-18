import type { OperationScopedParams, PageResult } from '../operations/shared'
import { MobileApiError, requestOperation } from '../operations/shared'

export { MobileApiError }
export type { OperationScopedParams, PageResult }

export type WorkflowTaskStatus = 'pending' | 'processing' | 'completed' | 'cancelled' | 'overdue'
export type WorkflowPriority = 'low' | 'normal' | 'high' | 'urgent'

export interface WorkflowTaskComment {
  id?: number
  content: string
  commenter_user_id?: number
  created_at?: string | null
}

export interface WorkflowTaskAttachment {
  id?: number
  file_name: string
  file_url?: string
  file_size?: number
}

export interface MobileWorkflowTask {
  id?: number
  task_id?: number
  task_no?: string
  task_type?: string
  title: string
  priority?: WorkflowPriority
  status: WorkflowTaskStatus | string
  source_type?: string | null
  source_id?: number | null
  due_at?: string | null
  completed_at?: string | null
  comments?: WorkflowTaskComment[]
  attachments?: WorkflowTaskAttachment[]
}

export interface WorkflowTaskParams extends OperationScopedParams {
  status?: WorkflowTaskStatus | string
  task_type?: string
}

export interface WorkflowTaskCompletePayload extends OperationScopedParams {
  result: string
  content: string
}

export interface WorkflowTaskCommentPayload extends OperationScopedParams {
  content: string
}

export interface WorkflowTaskCompleteResult {
  task_id: number
  status: WorkflowTaskStatus | string
}

export interface WorkflowTaskCommentResult {
  task_id: number
  commented: boolean
}

export function getWorkflowTasks(params?: WorkflowTaskParams): Promise<PageResult<MobileWorkflowTask>> {
  return requestOperation('/mobile/education/workflow/tasks/my', 'GET', params)
}

export async function getWorkflowTaskDetail(taskId: number, params?: WorkflowTaskParams): Promise<MobileWorkflowTask> {
  const result = await getWorkflowTasks(params)
  const task = result.list.find(item => workflowTaskId(item) === taskId)

  if (task) {
    return task
  }

  const error = new MobileApiError('workflow task is not assigned to current user')
  error.code = 403
  error.data = { task_id: taskId }
  throw error
}

export function completeWorkflowTask(taskId: number, payload: WorkflowTaskCompletePayload): Promise<WorkflowTaskCompleteResult> {
  return requestOperation(`/mobile/education/workflow/tasks/${taskId}/complete`, 'POST', payload)
}

export function addWorkflowTaskComment(taskId: number, payload: WorkflowTaskCommentPayload): Promise<WorkflowTaskCommentResult> {
  return requestOperation(`/mobile/education/workflow/tasks/${taskId}/comments`, 'POST', payload)
}

export function workflowTaskId(task: MobileWorkflowTask): number {
  return Number(task.task_id || task.id || 0)
}
