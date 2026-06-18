import type { LeaveRequestRecord, LeaveRequestStatus, LessonChangeRecord, LessonChangeStatus, LessonChangeType, MakeupLessonResult, RescheduleLessonResult } from '../../api/academic/lessonChange.ts'

export function canApproveLeave(status: LeaveRequestStatus, hasPermission: boolean): boolean {
  return hasPermission && status === 'pending'
}

export function canRejectLeave(status: LeaveRequestStatus, hasPermission: boolean): boolean {
  return hasPermission && status === 'pending'
}

export function canCancelLeave(status: LeaveRequestStatus, hasPermission: boolean): boolean {
  return hasPermission && ['pending', 'approved'].includes(status)
}

export function canCreateMakeup(status: LeaveRequestStatus, hasPermission: boolean, makeupLessonId?: number | null): boolean {
  return hasPermission && status === 'approved' && !makeupLessonId
}

export function canRescheduleLesson(hasPermission: boolean): boolean {
  return hasPermission
}

export function applyLeaveReviewSuccess(row: LeaveRequestRecord, status: LeaveRequestStatus, reviewRemark: string): LeaveRequestRecord {
  return { ...row, status, review_remark: reviewRemark }
}

export function reviewDialogErrorState(message: string): { visible: true, errorText: string } {
  return { visible: true, errorText: message }
}

export function leaveStatusType(status: LeaveRequestStatus): 'info' | 'success' | 'warning' | 'danger' {
  if (['approved', 'makeup_scheduled', 'closed'].includes(status)) {
    return 'success'
  }
  if (status === 'pending') {
    return 'warning'
  }
  if (['rejected', 'cancelled'].includes(status)) {
    return 'danger'
  }
  return 'info'
}

export function lessonChangeTypeLabel(type: LessonChangeType): string {
  return type === 'makeup' ? '补课' : '调课'
}

export function lessonChangeStatusType(status: LessonChangeStatus): 'success' | 'danger' {
  return status === 'confirmed' ? 'success' : 'danger'
}

export function makeupSuccessSummary(result: MakeupLessonResult): string {
  return `${result.target_lesson.id}/${result.change_record.change_no}`
}

export function rescheduleSuccessSummary(result: RescheduleLessonResult): string {
  return `${result.lesson.start_at}/${result.lesson.end_at}`
}

export function conflictMessage(error: any): string {
  const data = error?.data ?? error?.response?.data?.data ?? {}
  const firstConflict = Array.isArray(data.conflicts) ? data.conflicts[0] : data
  const type = firstConflict?.conflict_type
  const lessonIds = firstConflict?.lesson_ids
  if (type && Array.isArray(lessonIds)) {
    return `${type}: ${lessonIds.join(',')}`
  }

  return error?.message ?? '排课冲突'
}

export function detailDrawerTitle(row?: LessonChangeRecord | null): string {
  return row ? `${lessonChangeTypeLabel(row.change_type)} ${row.change_no}` : '调补课详情'
}

export function leaveStatusLabel(status?: string | null): string {
  const labels: Record<string, string> = {
    pending: '待审批',
    approved: '已通过',
    rejected: '已拒绝',
    cancelled: '已取消',
    makeup_scheduled: '已安排补课',
    closed: '已关闭',
  }

  return status ? labels[status] ?? status : '未知'
}

export function leaveSourceLabel(source?: string | null): string {
  const labels: Record<string, string> = {
    staff: '员工',
    guardian: '家长',
    teacher: '教师',
  }

  return source ? labels[source] ?? source : '未知'
}

export function leaveTypeLabel(type?: string | null): string {
  const labels: Record<string, string> = {
    sick: '病假',
    personal: '事假',
    school: '校内活动',
    other: '其他',
  }

  return type ? labels[type] ?? type : '未知'
}

export function lessonChangeStatusLabel(status?: string | null): string {
  const labels: Record<string, string> = {
    confirmed: '已确认',
    cancelled: '已取消',
  }

  return status ? labels[status] ?? status : '未知'
}
