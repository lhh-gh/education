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
  const accountText = result.account ? `课时账户 ${result.account.id}` : '待生成账户'
  const availableText = result.account ? result.account.available_units : '待确认'
  const separator = ' \u00B7 '

  return `${result.enrollment.enrollment_no}${separator}${accountText}${separator}可用课时 ${availableText}`
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

export function enrollmentStatusLabel(status?: string | null): string {
  const labels: Record<string, string> = {
    pending: '待确认',
    confirmed: '已确认',
    cancelled: '已取消',
  }

  return status ? labels[status] ?? status : '未知'
}

export function accountStatusLabel(status?: string | null): string {
  const labels: Record<string, string> = {
    active: '正常',
    frozen: '冻结',
    closed: '已关闭',
  }

  return status ? labels[status] ?? status : '未知'
}

export function accountStatusActionLabel(status: StudentCourseAccountStatus): string {
  const action = accountStatusAction(status)
  if (action === 'freeze') {
    return '冻结'
  }
  if (action === 'unfreeze') {
    return '解冻'
  }

  return '已关闭'
}

export function balanceLevelLabel(level?: string | null): string {
  const labels: Record<string, string> = {
    zero: '已耗尽',
    low: '低课时',
    normal: '正常',
    expired: '已过期',
    expiring_soon: '即将过期',
  }

  return level ? labels[level] ?? level : '未知'
}

export function ledgerRowsBySource<T extends Pick<AccountLedgerRecord, 'source_type'>>(rows: T[], sourceType?: AccountLedgerSourceType): T[] {
  return sourceType ? rows.filter(row => row.source_type === sourceType) : rows
}
