import type { MineResult } from '../foundation/types.ts'
import type { OperationPage, OperationScopedParams } from './types.ts'
import { operationGetOptions, operationRequestOptions } from './types.ts'

export type OperationLessonChangeType = 'reschedule' | 'suspend' | 'cancel' | 'replace_teacher' | 'replace_classroom' | 'substitute_teacher'
export type OperationLessonChangeStatus = 'pending' | 'approved' | 'rejected' | 'applied' | 'cancelled'

export interface LessonChangeRequestRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  lesson_id: number
  change_type: OperationLessonChangeType
  status: OperationLessonChangeStatus
  old_values_json: Record<string, unknown>
  new_values_json: Record<string, unknown>
  reason: string
  requested_by: number
  approved_by?: number | null
  approved_at?: string | null
  applied_at?: string | null
  created_at?: string | null
}

export interface LessonChangeLogRecord {
  id: number
  lesson_id: number
  change_request_id?: number | null
  change_type: OperationLessonChangeType
  before_json: Record<string, unknown>
  after_json: Record<string, unknown>
  operator_id: number
  created_at?: string | null
}

export interface LessonChangePageParams extends OperationScopedParams {
  lesson_id?: number
  teacher_id?: number
  status?: OperationLessonChangeStatus
  change_type?: OperationLessonChangeType
}

export interface LessonChangeCreatePayload {
  tenant_id?: number
  campus_id?: number
  lesson_id: number
  change_type: OperationLessonChangeType
  new_values_json: Record<string, unknown>
  reason: string
}

export interface LessonBatchChangePayload {
  tenant_id?: number
  campus_id?: number
  lesson_ids: number[]
  change_type: Extract<OperationLessonChangeType, 'suspend' | 'cancel' | 'replace_teacher' | 'replace_classroom'>
  reason: string
  new_values_json?: Record<string, unknown>
}

export interface LessonChangeReviewPayload {
  tenant_id?: number
  campus_id?: number
  review_note?: string
}

export function pageLessonChangeRequests(params: LessonChangePageParams): Promise<MineResult<OperationPage<LessonChangeRequestRecord>>> {
  return useHttp().get('/admin/education/operations/lesson-change-requests/page', operationGetOptions(params))
}

export function createLessonChangeRequest(payload: LessonChangeCreatePayload): Promise<MineResult<LessonChangeRequestRecord>> {
  return useHttp().post('/admin/education/operations/lesson-change-requests', payload, operationRequestOptions(payload))
}

export function approveLessonChangeRequest(id: number, payload: LessonChangeReviewPayload): Promise<MineResult<LessonChangeRequestRecord>> {
  return useHttp().post(`/admin/education/operations/lesson-change-requests/${id}/approve`, payload, operationRequestOptions(payload))
}

export function rejectLessonChangeRequest(id: number, payload: LessonChangeReviewPayload): Promise<MineResult<LessonChangeRequestRecord>> {
  return useHttp().post(`/admin/education/operations/lesson-change-requests/${id}/reject`, payload, operationRequestOptions(payload))
}

export function applyLessonChangeRequest(id: number, payload: { tenant_id?: number, campus_id?: number } = {}): Promise<MineResult<LessonChangeRequestRecord>> {
  return useHttp().post(`/admin/education/operations/lesson-change-requests/${id}/apply`, {}, operationRequestOptions(payload))
}

export function batchChangeLessons(payload: LessonBatchChangePayload): Promise<MineResult<{ success_ids: number[], failed: Array<{ lesson_id: number, message: string }> }>> {
  return useHttp().post('/admin/education/operations/lessons/batch-change', payload, operationRequestOptions(payload))
}

export function listLessonChangeLogs(params: OperationScopedParams & { lesson_id?: number, change_request_id?: number }): Promise<MineResult<OperationPage<LessonChangeLogRecord>>> {
  return useHttp().get('/admin/education/operations/lesson-change-logs/page', operationGetOptions(params))
}
