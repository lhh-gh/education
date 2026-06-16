import type { MinePage, MineResult, PageParams } from '../foundation/types.ts'

export type AcademicRecordStatus = 'enabled' | 'disabled'
export type ClassType = 'group' | 'one_to_one'
export type ClassStudentStatus = 'active' | 'paused' | 'left'
export type LessonStatus = 'scheduled' | 'cancelled' | 'completed'
export type LessonStudentStatus = 'planned' | 'cancelled'
export type ScheduleSourceType = 'manual' | 'batch'

export interface AcademicScopedPageParams extends Partial<PageParams> {
  page?: number
  page_size?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  keyword?: string
  status?: string
}

export interface ClassPageParams extends AcademicScopedPageParams {
  course_id?: number
  main_teacher_id?: number
  status?: AcademicRecordStatus
}

export interface LessonPageParams extends AcademicScopedPageParams {
  class_id?: number
  course_id?: number
  teacher_id?: number
  classroom_id?: number
  status?: LessonStatus
  start_at?: string
  end_at?: string
}

export interface CalendarLessonParams {
  tenant_id?: number
  campus_id?: number
  class_id?: number
  teacher_id?: number
  classroom_id?: number
  status?: LessonStatus
  start_at?: string
  end_at?: string
}

export interface ClassRecord {
  id: number
  tenant_id: number
  campus_id: number
  course_id: number
  course_name?: string
  main_teacher_id?: number | null
  main_teacher_name?: string | null
  classroom_id?: number | null
  classroom_name?: string | null
  code: string
  name: string
  class_type: ClassType
  max_students: number
  active_student_count?: number
  lesson_units: string
  start_date?: string | null
  end_date?: string | null
  schedule_note?: string | null
  status: AcademicRecordStatus
  remark?: string | null
  updated_at?: string
}

export interface ClassStudentRecord {
  id?: number
  class_id?: number
  student_id: number
  student_name?: string
  student_no?: string
  account_id?: number
  status?: ClassStudentStatus
  joined_at?: string
  left_at?: string | null
}

export interface LessonStudentRecord {
  id?: number
  lesson_id?: number
  student_id: number
  student_name_snapshot?: string
  student_no_snapshot?: string
  lesson_units?: string
  status?: LessonStudentStatus
}

export interface LessonRecord {
  id: number
  tenant_id?: number
  campus_id?: number
  lesson_no: string
  class_id: number
  class_name_snapshot: string
  course_id: number
  course_name_snapshot: string
  teacher_id: number
  teacher_name_snapshot: string
  classroom_id?: number | null
  classroom_name_snapshot?: string | null
  title: string
  start_at: string
  end_at: string
  lesson_units: string
  student_count: number
  status: LessonStatus
  source_type?: ScheduleSourceType
  schedule_batch_no?: string | null
  cancel_reason?: string | null
  students?: LessonStudentRecord[]
}

export interface ClassSavePayload {
  tenant_id?: number
  campus_id: number
  course_id: number
  main_teacher_id?: number | null
  classroom_id?: number | null
  code: string
  name: string
  class_type: ClassType
  max_students: number
  start_date?: string | null
  end_date?: string | null
  lesson_units: number
  status: AcademicRecordStatus
  schedule_note?: string | null
  remark?: string | null
}

export interface SingleLessonSchedulePayload {
  tenant_id?: number
  campus_id: number
  class_id: number
  teacher_id: number
  classroom_id?: number | null
  title: string
  start_at: string
  end_at: string
  lesson_units: number
  remark?: string | null
}

export interface BatchLessonSchedulePayload {
  tenant_id?: number
  campus_id: number
  class_id: number
  teacher_id: number
  classroom_id?: number | null
  title_template: string
  start_date: string
  end_date: string
  weekdays: number[]
  start_time: string
  end_time: string
  lesson_units: number
  remark?: string | null
}

export interface ScheduleConflictResult {
  has_conflict?: boolean
  conflicts?: Array<Record<string, unknown>>
  conflict_type?: string
  lesson_ids?: number[]
}

export interface SingleLessonScheduleResult {
  lesson: LessonRecord
  lesson_students: {
    created_count: number
    list: LessonStudentRecord[]
  }
}

export interface BatchLessonScheduleResult {
  schedule_batch_no?: string | null
  created_count: number
  lesson_ids: number[]
}

function tenantHeaders(tenantId?: number): { headers: Record<string, string> } | undefined {
  return tenantId && tenantId > 0 ? { headers: { 'X-Tenant-Id': String(tenantId) } } : undefined
}

function tenantIdFrom(paramsOrPayload: { tenant_id?: number }): number | undefined {
  return paramsOrPayload.tenant_id
}

export function pageClasses(params: ClassPageParams): Promise<MineResult<MinePage<ClassRecord>>> {
  return useHttp().get('/admin/education/academic/classes/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function createClass(data: ClassSavePayload): Promise<MineResult<ClassRecord>> {
  return useHttp().post('/admin/education/academic/classes', data, tenantHeaders(tenantIdFrom(data)))
}

export function updateClass(id: number, data: ClassSavePayload): Promise<MineResult<ClassRecord>> {
  return useHttp().put(`/admin/education/academic/classes/${id}`, data, tenantHeaders(tenantIdFrom(data)))
}

export function changeClassStatus(id: number, status: AcademicRecordStatus, tenantId?: number): Promise<MineResult<ClassRecord>> {
  return useHttp().put(`/admin/education/academic/classes/${id}/status`, { status }, tenantHeaders(tenantId))
}

export function deleteClass(id: number, tenantId?: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/academic/classes/${id}`, tenantHeaders(tenantId))
}

export function getClassStudents(id: number, tenantId?: number): Promise<MineResult<{ list: ClassStudentRecord[] }>> {
  return useHttp().get(`/admin/education/academic/classes/${id}/students`, tenantHeaders(tenantId))
}

export function saveClassStudents(id: number, payload: { students: Array<Record<string, unknown>> }, tenantId?: number): Promise<MineResult<Record<string, unknown>>> {
  return useHttp().put(`/admin/education/academic/classes/${id}/students`, payload, tenantHeaders(tenantId))
}

export function pageLessons(params: LessonPageParams): Promise<MineResult<MinePage<LessonRecord>>> {
  return useHttp().get('/admin/education/academic/lessons/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function getLesson(id: number, tenantId?: number): Promise<MineResult<LessonRecord & { students?: LessonStudentRecord[] }>> {
  return useHttp().get(`/admin/education/academic/lessons/${id}`, tenantHeaders(tenantId))
}

export function updateLesson(id: number, data: SingleLessonSchedulePayload): Promise<MineResult<LessonRecord>> {
  return useHttp().put(`/admin/education/academic/lessons/${id}`, data, tenantHeaders(tenantIdFrom(data)))
}

export function cancelLesson(id: number, cancel_reason: string, tenantId?: number): Promise<MineResult<LessonRecord>> {
  return useHttp().put(`/admin/education/academic/lessons/${id}/cancel`, { cancel_reason }, tenantHeaders(tenantId))
}

export function deleteLesson(id: number, tenantId?: number): Promise<MineResult<true>> {
  return useHttp().delete(`/admin/education/academic/lessons/${id}`, tenantHeaders(tenantId))
}

export function calendarLessons(params: CalendarLessonParams): Promise<MineResult<{ list: LessonRecord[] }>> {
  return useHttp().get('/admin/education/academic/lesson-schedule/calendar', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function checkScheduleConflict(payload: Record<string, unknown> & { tenant_id?: number }): Promise<MineResult<ScheduleConflictResult>> {
  return useHttp().post('/admin/education/academic/lesson-schedule/conflict-check', payload, tenantHeaders(tenantIdFrom(payload)))
}

export function scheduleSingleLesson(data: SingleLessonSchedulePayload): Promise<MineResult<SingleLessonScheduleResult>> {
  return useHttp().post('/admin/education/academic/lesson-schedule/single', data, tenantHeaders(tenantIdFrom(data)))
}

export function scheduleBatchLessons(data: BatchLessonSchedulePayload): Promise<MineResult<BatchLessonScheduleResult>> {
  return useHttp().post('/admin/education/academic/lesson-schedule/batch', data, tenantHeaders(tenantIdFrom(data)))
}
