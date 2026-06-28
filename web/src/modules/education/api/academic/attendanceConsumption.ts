import type { MinePage, MineResult, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

export type AttendanceStatus = 'present' | 'late' | 'absent' | 'leave'
export type ConsumptionPolicy = 'consume' | 'no_consume'
export type ConsumptionSourceType = 'attendance' | 'rollback'
export type LedgerDirection = 'decrease' | 'increase'
export type ConsumptionStatus = 'active' | 'reversed'
export type AccountAdjustmentType = 'supplement_deduction' | 'rollback'
export type AccountAdjustmentStatus = 'confirmed' | 'rolled_back'

export interface AcademicScopedPageParams extends Partial<PageParams> {
  page?: number
  page_size?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  keyword?: string
}

export interface AttendanceLessonPageParams extends AcademicScopedPageParams {
  class_id?: number
  teacher_id?: number
  status?: 'scheduled' | 'completed' | 'cancelled'
  start_at?: string
  end_at?: string
}

export interface ConsumptionPageParams extends AcademicScopedPageParams {
  account_id?: number
  student_id?: number
  course_id?: number
  lesson_id?: number
  source_type?: ConsumptionSourceType
  status?: ConsumptionStatus
}

export interface AccountAdjustmentPageParams extends AcademicScopedPageParams {
  account_id?: number
  student_id?: number
  course_id?: number
  adjustment_type?: AccountAdjustmentType
  status?: AccountAdjustmentStatus
}

export interface AttendanceLessonRecord {
  id: number
  lesson_no: string
  title: string
  class_name_snapshot: string
  course_name_snapshot?: string
  teacher_name_snapshot: string
  start_at: string
  end_at: string
  student_count: number
  status: 'scheduled' | 'completed' | 'cancelled'
}

export interface AttendanceLessonStudentRecord {
  id: number
  lesson_student_id?: number
  student_id: number
  student_name_snapshot: string
  student_no_snapshot?: string
  account_id: number
  lesson_units: string
  planned_units?: string
  account_available_units?: string
  status?: string
}

export interface AttendanceRecord {
  id: number
  lesson_student_id: number
  attendance_status: AttendanceStatus
  consume_policy: ConsumptionPolicy
  consumed_units: string
  consumption_status: 'none' | ConsumptionStatus
  remark?: string | null
}

export interface AttendanceLessonDetail {
  lesson: AttendanceLessonRecord | null
  lesson_students: AttendanceLessonStudentRecord[]
  attendances: AttendanceRecord[]
}

export interface AttendanceSubmitRecord {
  lesson_student_id: number
  attendance_status: AttendanceStatus
  consume_policy: ConsumptionPolicy
  consumed_units: number
  remark?: string | null
}

export interface AttendanceSubmitResult {
  lesson_id?: number
  attendance_batch_no?: string | null
  attendance_count: number
  consumed_count: number
  no_consume_count?: number
  total_consumed_units: string
  account_changes?: Array<{
    account_id: number
    before_available_units: string
    after_available_units: string
  }>
}

export interface ConsumptionRecord {
  id: number
  consumption_no: string
  account_id: number
  student_id: number
  student_name?: string
  course_id: number
  course_name?: string
  lesson_id: number
  lesson_no?: string
  source_type: ConsumptionSourceType
  direction: LedgerDirection
  units: string
  before_available_units: string
  after_available_units: string
  before_consumed_units?: string
  after_consumed_units?: string
  status: ConsumptionStatus
  original_consumption_id?: number
  reason?: string
  created_at?: string
}

export interface AccountAdjustmentRecord {
  id: number
  adjustment_no: string
  account_id: number
  student_id: number
  student_name?: string
  course_id: number
  course_name?: string
  adjustment_type: AccountAdjustmentType
  direction: LedgerDirection
  units: string
  before_available_units: string
  after_available_units: string
  before_adjusted_units?: string
  after_adjusted_units?: string
  status: AccountAdjustmentStatus
  original_adjustment_id?: number
  reason: string
  created_at?: string
}

export interface RollbackResult<T> {
  original: T
  rollback: T
  account?: Record<string, unknown>
}

function scopeOptions(input: { tenant_id?: number, campus_id?: number } | number = {}): { headers?: Record<string, string> } {
  return educationScopeRequestOptions(typeof input === 'number' ? { tenant_id: input } : input)
}

export function pageAttendanceLessons(params: AttendanceLessonPageParams): Promise<MineResult<MinePage<AttendanceLessonRecord>>> {
  return useHttp().get('/admin/education/academic/attendance/lessons/page', educationScopeGetOptions(params))
}

export function getAttendanceLesson(lessonId: number, tenantId?: number): Promise<MineResult<AttendanceLessonDetail>> {
  return useHttp().get(`/admin/education/academic/attendance/lessons/${lessonId}`, scopeOptions(tenantId))
}

export function submitAttendance(lessonId: number, payload: { tenant_id?: number, submitted_at?: string | null, records: AttendanceSubmitRecord[] }): Promise<MineResult<AttendanceSubmitResult>> {
  return useHttp().post(`/admin/education/academic/attendance/lessons/${lessonId}/submit`, payload, scopeOptions(payload))
}

export function pageConsumptions(params: ConsumptionPageParams): Promise<MineResult<MinePage<ConsumptionRecord>>> {
  return useHttp().get('/admin/education/academic/consumptions/page', educationScopeGetOptions(params))
}

export function getConsumption(id: number, tenantId?: number): Promise<MineResult<ConsumptionRecord>> {
  return useHttp().get(`/admin/education/academic/consumptions/${id}`, scopeOptions(tenantId))
}

export function rollbackConsumption(id: number, reason: string, tenantId?: number): Promise<MineResult<RollbackResult<ConsumptionRecord>>> {
  return useHttp().post(`/admin/education/academic/consumptions/${id}/rollback`, { reason }, scopeOptions(tenantId))
}

export function pageAccountAdjustments(params: AccountAdjustmentPageParams): Promise<MineResult<MinePage<AccountAdjustmentRecord>>> {
  return useHttp().get('/admin/education/academic/account-adjustments/page', educationScopeGetOptions(params))
}

export function getAccountAdjustment(id: number, tenantId?: number): Promise<MineResult<AccountAdjustmentRecord>> {
  return useHttp().get(`/admin/education/academic/account-adjustments/${id}`, scopeOptions(tenantId))
}

export function createSupplementDeduction(payload: { tenant_id?: number, account_id: number, units: number, reason: string }): Promise<MineResult<{ adjustment: AccountAdjustmentRecord, account: Record<string, unknown> }>> {
  return useHttp().post('/admin/education/academic/account-adjustments', payload, scopeOptions(payload))
}

export function rollbackAccountAdjustment(id: number, reason: string, tenantId?: number): Promise<MineResult<RollbackResult<AccountAdjustmentRecord>>> {
  return useHttp().post(`/admin/education/academic/account-adjustments/${id}/rollback`, { reason }, scopeOptions(tenantId))
}
