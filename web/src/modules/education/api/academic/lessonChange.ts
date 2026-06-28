import type { MinePage, MineResult, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

export type LeaveRequestSource = 'staff' | 'guardian' | 'teacher'
export type LeaveType = 'sick' | 'personal' | 'school' | 'other'
export type LeaveRequestStatus = 'pending' | 'approved' | 'rejected' | 'cancelled' | 'makeup_scheduled' | 'closed'
export type LessonChangeType = 'makeup' | 'reschedule'
export type LessonChangeStatus = 'confirmed' | 'cancelled'

export interface AcademicScopedPageParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  keyword?: string
}

export interface LeaveRequestPageParams extends AcademicScopedPageParams {
  student_id?: number
  class_id?: number
  lesson_id?: number
  source?: LeaveRequestSource
  status?: LeaveRequestStatus
}

export interface LessonChangePageParams extends AcademicScopedPageParams {
  change_type?: LessonChangeType
  status?: LessonChangeStatus
  source_lesson_id?: number
  target_lesson_id?: number
  student_id?: number
}

export interface LeaveRequestRecord {
  id: number
  tenant_id: number
  campus_id: number
  leave_no: string
  source: LeaveRequestSource
  leave_type: LeaveType
  lesson_id: number
  lesson_student_id: number
  class_id: number
  course_id: number
  student_id: number
  student_name?: string
  account_id: number
  guardian_id?: number | null
  teacher_id?: number | null
  reason: string
  status: LeaveRequestStatus
  requested_at?: string | null
  reviewed_at?: string | null
  reviewed_by?: number | null
  review_remark?: string | null
  cancelled_at?: string | null
  cancelled_by?: number | null
  cancel_reason?: string | null
  makeup_required: boolean
  makeup_lesson_id?: number | null
  remark?: string | null
  created_at?: string | null
  updated_at?: string | null
}

export interface LessonChangeRecord {
  id: number
  tenant_id: number
  campus_id: number
  change_no: string
  change_type: LessonChangeType
  status: LessonChangeStatus
  leave_request_id?: number | null
  source_lesson_id: number
  source_lesson_student_id?: number | null
  target_lesson_id?: number | null
  class_id: number
  course_id: number
  student_id?: number | null
  account_id?: number | null
  source_teacher_id?: number | null
  target_teacher_id?: number | null
  source_classroom_id?: number | null
  target_classroom_id?: number | null
  source_start_at?: string | null
  source_end_at?: string | null
  target_start_at?: string | null
  target_end_at?: string | null
  lesson_units: string
  reason: string
  cancelled_at?: string | null
  cancelled_by?: number | null
  cancel_reason?: string | null
  created_at?: string | null
}

export interface LeaveRequestCreatePayload {
  tenant_id?: number
  lesson_student_id: number
  source: LeaveRequestSource
  leave_type: LeaveType
  guardian_id?: number
  teacher_id?: number
  reason: string
  makeup_required?: boolean
  remark?: string
}

export interface MakeupLessonPayload {
  tenant_id?: number
  leave_request_id: number
  teacher_id: number
  classroom_id?: number
  title: string
  start_at: string
  end_at: string
  lesson_units: number
  reason: string
}

export interface RescheduleLessonPayload {
  tenant_id?: number
  source_lesson_id: number
  teacher_id: number
  classroom_id?: number
  title: string
  start_at: string
  end_at: string
  lesson_units: number
  reason: string
}

export interface MakeupLessonResult {
  leave_request: LeaveRequestRecord
  target_lesson: Record<string, any>
  target_lesson_student: Record<string, any>
  change_record: LessonChangeRecord
}

export interface RescheduleLessonResult {
  lesson: Record<string, any>
  change_record: LessonChangeRecord
}

function scopeOptions(input: { tenant_id?: number, campus_id?: number } | number = {}): { headers?: Record<string, string> } {
  return educationScopeRequestOptions(typeof input === 'number' ? { tenant_id: input } : input)
}

export function pageLeaveRequests(params: LeaveRequestPageParams): Promise<MineResult<MinePage<LeaveRequestRecord>>> {
  return useHttp().get('/admin/education/academic/leave-requests/page', educationScopeGetOptions(params))
}

export function getLeaveRequest(id: number, tenantId?: number): Promise<MineResult<LeaveRequestRecord>> {
  return useHttp().get(`/admin/education/academic/leave-requests/${id}`, scopeOptions(tenantId))
}

export function createLeaveRequest(payload: LeaveRequestCreatePayload): Promise<MineResult<LeaveRequestRecord>> {
  return useHttp().post('/admin/education/academic/leave-requests', payload, scopeOptions(payload))
}

export function approveLeaveRequest(id: number, reviewRemark: string, tenantId?: number): Promise<MineResult<LeaveRequestRecord>> {
  return useHttp().put(`/admin/education/academic/leave-requests/${id}/approve`, { review_remark: reviewRemark }, scopeOptions(tenantId))
}

export function rejectLeaveRequest(id: number, reviewRemark: string, tenantId?: number): Promise<MineResult<LeaveRequestRecord>> {
  return useHttp().put(`/admin/education/academic/leave-requests/${id}/reject`, { review_remark: reviewRemark }, scopeOptions(tenantId))
}

export function cancelLeaveRequest(id: number, cancelReason: string, tenantId?: number): Promise<MineResult<LeaveRequestRecord>> {
  return useHttp().put(`/admin/education/academic/leave-requests/${id}/cancel`, { cancel_reason: cancelReason }, scopeOptions(tenantId))
}

export function pageLessonChanges(params: LessonChangePageParams): Promise<MineResult<MinePage<LessonChangeRecord>>> {
  return useHttp().get('/admin/education/academic/lesson-changes/page', educationScopeGetOptions(params))
}

export function getLessonChange(id: number, tenantId?: number): Promise<MineResult<LessonChangeRecord>> {
  return useHttp().get(`/admin/education/academic/lesson-changes/${id}`, scopeOptions(tenantId))
}

export function createMakeupLesson(payload: MakeupLessonPayload): Promise<MineResult<MakeupLessonResult>> {
  return useHttp().post('/admin/education/academic/lesson-changes/makeup', payload, scopeOptions(payload))
}

export function rescheduleLesson(payload: RescheduleLessonPayload): Promise<MineResult<RescheduleLessonResult>> {
  return useHttp().post('/admin/education/academic/lesson-changes/reschedule', payload, scopeOptions(payload))
}
