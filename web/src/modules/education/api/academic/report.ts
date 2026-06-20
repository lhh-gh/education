import type { MineResult, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

export type ReportGroupBy = 'date' | 'campus' | 'class' | 'teacher' | 'course' | 'status' | 'source_type'
export type AttendanceStatus = 'present' | 'late' | 'absent' | 'leave'
export type ConsumptionSourceType = 'attendance' | 'rollback'
export type ConsumptionStatus = 'active' | 'reversed'
export type AccountBalanceLevel = 'zero' | 'low' | 'normal' | 'expired' | 'expiring_soon'
export type V1AcceptanceStatus = 'pass' | 'fail'

export interface TenantScopedParams {
  tenant_id?: number
  campus_id?: number
}

export interface DashboardReportParams extends TenantScopedParams {
  start_at?: string
  end_at?: string
}

export interface ReportPageParams extends Partial<PageParams>, TenantScopedParams {
  page?: number
  pageSize?: number
  start_at?: string
  end_at?: string
  group_by?: ReportGroupBy
}

export interface AttendanceReportParams extends ReportPageParams {
  class_id?: number
  teacher_id?: number
  course_id?: number
  attendance_status?: AttendanceStatus
}

export interface ConsumptionReportParams extends ReportPageParams {
  course_id?: number
  class_id?: number
  student_id?: number
  account_id?: number
  source_type?: ConsumptionSourceType
  status?: ConsumptionStatus
}

export interface AccountBalanceReportParams extends Partial<PageParams>, TenantScopedParams {
  page?: number
  pageSize?: number
  course_id?: number
  student_id?: number
  status?: 'active' | 'frozen' | 'closed'
  balance_level?: AccountBalanceLevel
}

export interface LeaveReportParams extends ReportPageParams {
  class_id?: number
  teacher_id?: number
  course_id?: number
  source?: 'staff' | 'guardian' | 'teacher'
  leave_type?: 'sick' | 'personal' | 'school' | 'other'
  status?: 'pending' | 'approved' | 'rejected' | 'cancelled' | 'makeup_scheduled' | 'closed'
}

export interface V1AcceptanceParams extends TenantScopedParams {
  include_detail?: boolean
}

export interface AcademicDashboardResult {
  range: {
    start_at: string
    end_at: string
  }
  campus_id?: number | null
  metrics: {
    student_count: number
    active_student_count: number
    guardian_count: number
    teacher_count: number
    active_class_count: number
    scheduled_lesson_count: number
    completed_lesson_count: number
    cancelled_lesson_count: number
    attendance_count: number
    present_count: number
    late_count: number
    absent_count: number
    leave_count: number
    consumed_units: string
    rollback_units: string
    net_consumed_units: string
    total_available_units: string
    frozen_units: string
    low_balance_account_count: number
    expiring_account_count: number
    pending_leave_count: number
    approved_leave_count: number
    makeup_scheduled_count: number
    published_notice_count: number
    unread_notice_receipt_count: number
  }
  trends: Array<{
    date: string
    scheduled_lesson_count: number
    completed_lesson_count: number
    consumed_units: string
  }>
  alerts: Array<{
    type: string
    level: 'info' | 'warning' | 'danger'
    title: string
    count: number
  }>
}

export interface AttendanceReportSummary {
  total_records: number
  present_count: number
  late_count: number
  absent_count: number
  leave_count: number
  attendance_rate: string
  leave_rate: string
}

export interface AttendanceReportRow {
  date: string
  campus_id: number
  campus_name?: string | null
  class_id: number
  class_name?: string | null
  teacher_id: number
  teacher_name?: string | null
  course_id: number
  course_name?: string | null
  lesson_id: number
  lesson_title?: string | null
  student_id: number
  student_name?: string | null
  attendance_status: AttendanceStatus
  consume_policy: 'consume' | 'no_consume'
  consumed_units: string
  submitted_at?: string | null
}

export interface ConsumptionReportSummary {
  decrease_units: string
  rollback_units: string
  net_units: string
  active_row_count: number
  reversed_row_count: number
}

export interface ConsumptionReportRow {
  date: string
  campus_id: number
  campus_name?: string | null
  consumption_no: string
  account_id: number
  student_id: number
  student_name?: string | null
  course_id: number
  course_name?: string | null
  class_id?: number | null
  class_name?: string | null
  teacher_id?: number | null
  teacher_name?: string | null
  lesson_id: number
  lesson_title?: string | null
  source_type: ConsumptionSourceType
  direction: 'decrease' | 'increase'
  units: string
  before_available_units: string
  after_available_units: string
  status: ConsumptionStatus
  created_at?: string | null
}

export interface AccountBalanceReportSummary {
  account_count: number
  active_count: number
  frozen_count: number
  closed_count: number
  total_purchased_units: string
  total_bonus_units: string
  total_consumed_units: string
  total_adjusted_units: string
  total_refunded_units: string
  total_frozen_units: string
  total_available_units: string
  low_balance_count: number
  expiring_count: number
}

export interface AccountBalanceReportRow {
  account_id: number
  campus_id: number
  campus_name?: string | null
  student_id: number
  student_name?: string | null
  student_no?: string | null
  course_id: number
  course_name?: string | null
  purchased_units: string
  bonus_units: string
  consumed_units: string
  adjusted_units: string
  refunded_units: string
  frozen_units: string
  available_units: string
  status: 'active' | 'frozen' | 'closed'
  balance_level: AccountBalanceLevel
  opened_at?: string | null
  expires_at?: string | null
}

export interface LeaveReportSummary {
  total_count: number
  pending_count: number
  approved_count: number
  rejected_count: number
  cancelled_count: number
  makeup_scheduled_count: number
  guardian_source_count: number
  teacher_source_count: number
  staff_source_count: number
}

export interface LeaveReportRow {
  leave_id: number
  leave_no: string
  source: 'staff' | 'guardian' | 'teacher'
  leave_type: 'sick' | 'personal' | 'school' | 'other'
  status: LeaveReportParams['status']
  campus_id: number
  campus_name?: string | null
  class_id: number
  class_name?: string | null
  teacher_id?: number | null
  teacher_name?: string | null
  course_id: number
  course_name?: string | null
  student_id: number
  student_name?: string | null
  lesson_id: number
  lesson_title?: string | null
  requested_at?: string | null
  reviewed_at?: string | null
  makeup_required: boolean
}

export interface ReportPageResult<Row, Summary> {
  summary: Summary
  list: Row[]
  total: number
  page?: number
  pageSize?: number
}

export interface V1AcceptanceGate {
  key: string
  name: string
  status: V1AcceptanceStatus
  message: string
  evidence: Record<string, unknown>
}

export interface V1LedgerMismatch {
  account_id: number
  student_id: number
  course_id: number
  actual_consumed_units: string
  expected_consumed_units: string
  actual_available_units: string
  expected_available_units: string
}

export interface V1AcceptanceSummary {
  overall_status: V1AcceptanceStatus
  checked_at: string
  gates: V1AcceptanceGate[]
  ledger: {
    account_count: number
    mismatch_count: number
    mismatches: V1LedgerMismatch[]
  }
  modules: Record<string, V1AcceptanceStatus>
  next_action: string
}

function scopeOptions(input: { tenant_id?: number, campus_id?: number } = {}): { headers?: Record<string, string> } {
  return educationScopeRequestOptions(input)
}

export function getAcademicDashboard(params: DashboardReportParams): Promise<MineResult<AcademicDashboardResult>> {
  return useHttp().get('/admin/education/academic/reports/dashboard', educationScopeGetOptions(params))
}

export function pageAttendanceReport(params: AttendanceReportParams): Promise<MineResult<ReportPageResult<AttendanceReportRow, AttendanceReportSummary>>> {
  return useHttp().get('/admin/education/academic/reports/attendance', educationScopeGetOptions(params))
}

export function pageConsumptionReport(params: ConsumptionReportParams): Promise<MineResult<ReportPageResult<ConsumptionReportRow, ConsumptionReportSummary>>> {
  return useHttp().get('/admin/education/academic/reports/consumption', educationScopeGetOptions(params))
}

export function pageAccountBalanceReport(params: AccountBalanceReportParams): Promise<MineResult<ReportPageResult<AccountBalanceReportRow, AccountBalanceReportSummary>>> {
  return useHttp().get('/admin/education/academic/reports/account-balances', educationScopeGetOptions(params))
}

export function pageLeaveReport(params: LeaveReportParams): Promise<MineResult<ReportPageResult<LeaveReportRow, LeaveReportSummary>>> {
  return useHttp().get('/admin/education/academic/reports/leaves', educationScopeGetOptions(params))
}

export function getV1AcceptanceSummary(params: V1AcceptanceParams): Promise<MineResult<V1AcceptanceSummary>> {
  return useHttp().get('/admin/education/academic/reports/v1-acceptance-summary', educationScopeGetOptions(params))
}
