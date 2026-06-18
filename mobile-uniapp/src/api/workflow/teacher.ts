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

export function getTeacherWorkflowTasks(params?: WorkflowTaskParams): Promise<PageResult<MobileWorkflowTask>> {
  return getWorkflowTasks(params)
}

export function getTeacherWorkflowTaskDetail(taskId: number, params?: WorkflowTaskParams): Promise<MobileWorkflowTask> {
  return getWorkflowTaskDetail(taskId, params)
}

export function completeTeacherWorkflowTask(taskId: number, payload: WorkflowTaskCompletePayload): Promise<WorkflowTaskCompleteResult> {
  return completeWorkflowTask(taskId, payload)
}

export function addTeacherWorkflowTaskComment(taskId: number, payload: WorkflowTaskCommentPayload): Promise<WorkflowTaskCommentResult> {
  return addWorkflowTaskComment(taskId, payload)
}
