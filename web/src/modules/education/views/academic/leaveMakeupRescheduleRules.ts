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
  return type === 'makeup' ? 'Make-up' : 'Reschedule'
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

  return error?.message ?? 'Schedule conflict'
}

export function detailDrawerTitle(row?: LessonChangeRecord | null): string {
  return row ? `${lessonChangeTypeLabel(row.change_type)} ${row.change_no}` : 'Lesson Change Detail'
}
