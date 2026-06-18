import type {
  MobileWorkflowTask,
  WorkflowTaskCommentPayload,
  WorkflowTaskCommentResult,
  WorkflowTaskCompletePayload,
  WorkflowTaskCompleteResult,
  WorkflowTaskParams,
  WorkflowTaskStatus,
} from './shared'
import {
  addWorkflowTaskComment,
  completeWorkflowTask,
  getWorkflowTaskDetail,
  getWorkflowTasks,
  MobileApiError,
} from './shared'
import type { PageResult } from './shared'

export { MobileApiError }
export type {
  MobileWorkflowTask,
  WorkflowTaskCommentPayload,
  WorkflowTaskCompletePayload,
  WorkflowTaskParams,
  WorkflowTaskStatus,
}

export function getOperatorWorkflowTasks(params?: WorkflowTaskParams): Promise<PageResult<MobileWorkflowTask>> {
  return getWorkflowTasks(params)
}

export function getOperatorWorkflowTaskDetail(taskId: number, params?: WorkflowTaskParams): Promise<MobileWorkflowTask> {
  return getWorkflowTaskDetail(taskId, params)
}

export function completeOperatorWorkflowTask(taskId: number, payload: WorkflowTaskCompletePayload): Promise<WorkflowTaskCompleteResult> {
  return completeWorkflowTask(taskId, payload)
}

export function addOperatorWorkflowTaskComment(taskId: number, payload: WorkflowTaskCommentPayload): Promise<WorkflowTaskCommentResult> {
  return addWorkflowTaskComment(taskId, payload)
}
