import type { AcademicRecordStatus } from '../../api/academic/classSchedule.ts'
import { hasPermission } from './actionRules.ts'

export interface ClassScheduleActionState {
  canCreate: boolean
  canEdit: boolean
  canStatus: boolean
  canDelete: boolean
  canStudentPage: boolean
  canStudentSave: boolean
  statusAction: 'enable' | 'disable'
}

export interface VisibleFailureState {
  visible: boolean
  message: string
}

export function classQuery(search: Record<string, unknown>): Record<string, unknown> {
  return {
    page: search.page,
    page_size: search.page_size,
    tenant_id: search.tenant_id,
    campus_id: search.campus_id,
    course_id: search.course_id,
    main_teacher_id: search.main_teacher_id,
    keyword: search.keyword,
    status: search.status,
  }
}

export function lessonQuery(search: Record<string, unknown>): Record<string, unknown> {
  return {
    page: search.page,
    page_size: search.page_size,
    campus_id: search.campus_id,
    class_id: search.class_id,
    course_id: search.course_id,
    teacher_id: search.teacher_id,
    classroom_id: search.classroom_id,
    status: search.status,
    start_at: search.start_at,
    end_at: search.end_at,
    keyword: search.keyword,
  }
}

export function calendarQuery(search: Record<string, unknown>): Record<string, unknown> {
  return {
    campus_id: search.campus_id,
    class_id: search.class_id,
    teacher_id: search.teacher_id,
    classroom_id: search.classroom_id,
    status: search.status,
    start_at: search.start_at,
    end_at: search.end_at,
  }
}

export function classActionsByPermission(codes: string[], status: AcademicRecordStatus): ClassScheduleActionState {
  return {
    canCreate: hasPermission(codes, 'education:academic:class:create'),
    canEdit: hasPermission(codes, 'education:academic:class:update'),
    canStatus: hasPermission(codes, 'education:academic:class:status'),
    canDelete: hasPermission(codes, 'education:academic:class:delete'),
    canStudentPage: hasPermission(codes, 'education:academic:class-student:page'),
    canStudentSave: hasPermission(codes, 'education:academic:class-student:save'),
    statusAction: status === 'enabled' ? 'disable' : 'enable',
  }
}

export function classFormStateAfterFailure(error: { message?: string }): VisibleFailureState {
  return { visible: true, message: error.message ?? '班级保存失败' }
}

export function classStudentDrawerStateAfterFailure(error: { message?: string }): VisibleFailureState {
  return { visible: true, message: error.message ?? '班级学员保存失败' }
}

export function scheduleDrawerStateAfterFailure(error: { message?: string }): VisibleFailureState {
  return { visible: true, message: error.message ?? '排课失败' }
}

export function classStudentSavePayload(values: Array<number | string>): { students: Array<{ student_id: number }> } {
  const ids = Array.from(new Set(values
    .map(value => Number(value))
    .filter(value => Number.isInteger(value) && value > 0)))

  return { students: ids.map(student_id => ({ student_id })) }
}

function nullableNumber(value: unknown): number | null {
  const numeric = Number(value)
  return Number.isInteger(numeric) && numeric > 0 ? numeric : null
}

function nullableText(value: unknown): string | null {
  return typeof value === 'string' && value.trim() !== '' ? value : null
}

export function singleSchedulePayload(form: Record<string, unknown>): Record<string, unknown> {
  return {
    campus_id: Number(form.campus_id),
    class_id: Number(form.class_id),
    teacher_id: Number(form.teacher_id),
    classroom_id: nullableNumber(form.classroom_id),
    title: String(form.title ?? ''),
    start_at: String(form.start_at ?? ''),
    end_at: String(form.end_at ?? ''),
    lesson_units: Number(form.lesson_units),
    remark: nullableText(form.remark),
  }
}

export function batchSchedulePayload(form: Record<string, unknown>): Record<string, unknown> {
  const weekdays = Array.isArray(form.weekdays)
    ? Array.from(new Set(form.weekdays.map(value => Number(value)).filter(value => Number.isInteger(value))))
    : []

  return {
    campus_id: Number(form.campus_id),
    class_id: Number(form.class_id),
    teacher_id: Number(form.teacher_id),
    classroom_id: nullableNumber(form.classroom_id),
    title_template: String(form.title_template ?? ''),
    start_date: String(form.start_date ?? ''),
    end_date: String(form.end_date ?? ''),
    weekdays,
    start_time: String(form.start_time ?? ''),
    end_time: String(form.end_time ?? ''),
    lesson_units: Number(form.lesson_units),
    remark: nullableText(form.remark),
  }
}

export function singleScheduleSuccessSummary(result: any): string {
  return `${result.lesson?.lesson_no ?? result.lesson?.id ?? 'lesson'} · students ${result.lesson_students?.created_count ?? 0}`
}

export function batchScheduleSuccessSummary(result: any): string {
  return `${result.schedule_batch_no ?? 'batch'} · created ${result.created_count ?? 0}`
}

export function calendarEventSummary(event: any): string {
  return [event.title, event.teacher_name_snapshot, event.classroom_name_snapshot].filter(Boolean).join(' · ')
}

export function conflictDrawerRows(result: any): Array<Record<string, unknown>> {
  const conflicts = Array.isArray(result?.conflicts) ? result.conflicts : []

  return conflicts.flatMap((conflict: any) => {
    const lessonIds = Array.isArray(conflict.lesson_ids) ? conflict.lesson_ids : []
    if (lessonIds.length === 0) {
      return [{ ...conflict, lesson_id: conflict.lesson_id }]
    }

    return lessonIds.map((lesson_id: number) => ({
      conflict_type: conflict.conflict_type,
      lesson_id,
      student_id: conflict.student_id,
    }))
  })
}

export function lessonStatusLabel(status?: string | null): string {
  const labels: Record<string, string> = {
    scheduled: '待上课',
    completed: '已完成',
    cancelled: '已取消',
  }

  return status ? labels[status] ?? status : '未知'
}

export function lessonStatusTagType(status?: string | null): 'success' | 'warning' | 'danger' | 'info' {
  if (status === 'completed') {
    return 'success'
  }
  if (status === 'scheduled') {
    return 'warning'
  }
  if (status === 'cancelled') {
    return 'danger'
  }

  return 'info'
}

export function batchConflictCalendarRows<T>(rows: T[], result: { has_conflict?: boolean }): T[] {
  return result.has_conflict ? rows : rows
}

export function lessonDetailStudentRows(detail: any): Array<Record<string, unknown>> {
  if (Array.isArray(detail?.students)) {
    return detail.students
  }

  return Array.isArray(detail?.lesson_students) ? detail.lesson_students : []
}

export function lessonRowsAfterCancel<T extends { id: number, status?: string }>(rows: T[], lesson: { id: number, status: string }): T[] {
  return rows.map(row => row.id === lesson.id ? { ...row, status: lesson.status } : row)
}

export function lessonRowsAfterDeleteFailure<T>(rows: T[], _error: { code?: number }): T[] {
  return rows
}
