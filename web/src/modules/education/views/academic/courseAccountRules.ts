import type { AcademicRecordStatus, AccountLedgerRecord, AccountLedgerSourceType, EnrollmentCreateResult, StudentCourseAccountStatus } from '../../api/academic/courseAccount.ts'
import { hasPermission } from './actionRules.ts'

export interface CourseAccountActionState {
  canCreate: boolean
  canEdit: boolean
  canStatus: boolean
  canDelete: boolean
  teacherAction: boolean
  statusAction: 'enable' | 'disable'
}

export function courseQuery(search: Record<string, unknown>): Record<string, unknown> {
  return {
    page: search.page,
    page_size: search.page_size,
    tenant_id: search.tenant_id,
    campus_id: search.campus_id,
    keyword: search.keyword,
    status: search.status,
  }
}

export function lessonPackageQuery(search: Record<string, unknown>): Record<string, unknown> {
  return {
    page: search.page,
    page_size: search.page_size,
    tenant_id: search.tenant_id,
    campus_id: search.campus_id,
    course_id: search.course_id,
    keyword: search.keyword,
    status: search.status,
  }
}

export function enrollmentQuery(search: Record<string, unknown>): Record<string, unknown> {
  return {
    page: search.page,
    page_size: search.page_size,
    campus_id: search.campus_id,
    student_id: search.student_id,
    course_id: search.course_id,
    status: search.status,
    enrolled_at_start: search.enrolled_at_start,
    enrolled_at_end: search.enrolled_at_end,
    keyword: search.keyword,
  }
}

export function courseActionsByPermission(codes: string[], status: AcademicRecordStatus): CourseAccountActionState {
  return {
    canCreate: hasPermission(codes, 'education:academic:course:create'),
    canEdit: hasPermission(codes, 'education:academic:course:update'),
    canStatus: hasPermission(codes, 'education:academic:course:status'),
    canDelete: hasPermission(codes, 'education:academic:course:delete'),
    teacherAction: hasPermission(codes, 'education:academic:course-teacher:page') || hasPermission(codes, 'education:academic:course-teacher:save'),
    statusAction: status === 'enabled' ? 'disable' : 'enable',
  }
}

export function normalizeTeacherSelection(values: Array<number | string>): number[] {
  return Array.from(new Set(values
    .map(value => Number(value))
    .filter(value => Number.isInteger(value) && value > 0)))
}

export function computePackageTotal(lessonUnits: number | string, bonusUnits: number | string): string {
  const lesson = Number(lessonUnits) || 0
  const bonus = Number(bonusUnits) || 0
  return (lesson + bonus).toFixed(2)
}

export function enrollmentSuccessSummary(result: EnrollmentCreateResult): string {
  const accountText = result.account ? `account ${result.account.id}` : 'pending account'
  const availableText = result.account ? result.account.available_units : 'pending'
  const separator = ' \u00b7 '

  return `${result.enrollment.enrollment_no}${separator}${accountText}${separator}available ${availableText}`
}

export function accountStatusAction(status: StudentCourseAccountStatus): 'freeze' | 'unfreeze' | 'closed' {
  if (status === 'active') {
    return 'freeze'
  }
  if (status === 'frozen') {
    return 'unfreeze'
  }

  return 'closed'
}

export function ledgerRowsBySource<T extends Pick<AccountLedgerRecord, 'source_type'>>(rows: T[], sourceType?: AccountLedgerSourceType): T[] {
  return sourceType ? rows.filter(row => row.source_type === sourceType) : rows
}
