import type { OperationScopedParams, PageResult } from './shared'
import { requestOperation } from './shared'

export { MobileApiError } from './shared'

export type ChangedLessonStatus = 'pending_today' | 'upcoming' | 'applied'
export type MakeupAttendanceStatus = 'present' | 'late' | 'absent' | 'leave'

export interface TeacherChangedLesson {
  id: number
  lesson_id: number
  change_type: string
  status: string
  reason?: string
  old_values_json?: Record<string, unknown>
  new_values_json?: Record<string, unknown>
  created_at?: string | null
}

export interface TeacherChangedLessonParams extends OperationScopedParams {
  status?: ChangedLessonStatus
}

export interface MakeupAttendanceDetail {
  makeup_record_id: number
  makeup_lesson_id: number
  student_id: number
  status: string
  attendance_status?: MakeupAttendanceStatus
}

export interface MakeupAttendancePayload extends OperationScopedParams {
  makeup_record_id: number
  attendance_status: MakeupAttendanceStatus
}

export interface TeacherWorkloadSummaryParams extends OperationScopedParams {
  month?: string
}

export interface TeacherWorkloadSummary {
  teacher_id?: number
  total_credits: string
  row_count: number
  student_count: number
  present_count: number
}

export function getChangedLessons(params?: TeacherChangedLessonParams): Promise<PageResult<TeacherChangedLesson>> {
  return requestOperation('/mobile/education/operations/teacher/changed-lessons', 'GET', params)
}

export function getMakeupAttendanceDetail(makeupRecordId: number, params?: OperationScopedParams): Promise<MakeupAttendanceDetail> {
  return requestOperation(`/mobile/education/operations/teacher/makeup-records/${makeupRecordId}`, 'GET', params)
}

export function submitMakeupAttendance(payload: MakeupAttendancePayload): Promise<{ makeup_record_id: number, entitlement_status: string }> {
  return requestOperation('/mobile/education/operations/teacher/makeup-attendance', 'POST', payload)
}

export function getWorkloadSummary(params?: TeacherWorkloadSummaryParams): Promise<TeacherWorkloadSummary> {
  return requestOperation('/mobile/education/operations/teacher/workload-summary', 'GET', params)
}
