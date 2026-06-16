import { MobileApiError } from '../foundation/context'

export { MobileApiError }

export type TeacherLessonStatus = 'scheduled' | 'cancelled' | 'completed'
export type TeacherAttendanceStatus = 'present' | 'late' | 'absent' | 'leave'
export type TeacherConsumePolicy = 'consume' | 'no_consume'
export type TeacherLeaveStatus = 'pending' | 'approved' | 'rejected' | 'cancelled' | 'makeup_scheduled' | 'closed'

export interface PageResult<T> {
  list: T[]
  total: number
  page: number
  pageSize: number
}

export interface TeacherCampusQuery {
  campus_id?: number
}

export interface TeacherTodayLessonParams extends TeacherCampusQuery {
  date?: string
}

export interface TeacherLessonPageParams extends TeacherCampusQuery {
  page?: number
  pageSize?: number
  start_at: string
  end_at: string
  status?: TeacherLessonStatus
  keyword?: string
}

export interface TeacherLessonCard {
  id: number
  lesson_no?: string
  title: string
  status: TeacherLessonStatus
  start_at: string
  end_at: string
  class_name_snapshot?: string
  course_name_snapshot?: string
  classroom_name_snapshot?: string | null
  attendance_submitted?: boolean
  can_submit_attendance?: boolean
  pending_leave_count?: number
}

export interface TeacherTodayLessonResult {
  date: string
  list: TeacherLessonCard[]
}

export interface TeacherLessonStudent {
  lesson_student_id: number
  student_id: number
  student_name_snapshot: string
  student_no_snapshot?: string
  lesson_units: string
  status: string
}

export interface TeacherLessonDetail extends TeacherLessonCard {
  lesson_students: TeacherLessonStudent[]
}

export interface TeacherAttendanceRecord {
  lesson_student_id: number
  student_id: number
  student_name_snapshot: string
  default_attendance_status: TeacherAttendanceStatus
  default_consume_policy: TeacherConsumePolicy
  default_consumed_units: string
  attendance_status?: TeacherAttendanceStatus
  consume_policy?: TeacherConsumePolicy
  consumed_units?: string
  remark?: string | null
}

export interface TeacherAttendanceSheet {
  lesson: TeacherLessonCard
  submitted: boolean
  records: TeacherAttendanceRecord[]
  summary: Record<string, unknown>
}

export interface TeacherAttendanceSubmitRecord {
  lesson_student_id: number
  attendance_status: TeacherAttendanceStatus
  consume_policy: TeacherConsumePolicy
  consumed_units: string | number
  remark?: string
}

export interface TeacherAttendanceSubmitPayload {
  submitted_at?: string
  records: TeacherAttendanceSubmitRecord[]
}

export interface TeacherAttendanceResult {
  lesson_id: number
  attendance_count: number
  total_consumed_units: string
  batch_no?: string
  counts?: Record<string, number>
  rows?: unknown[]
  account_changes?: unknown[]
}

export interface TeacherLeavePageParams extends TeacherCampusQuery {
  page?: number
  pageSize?: number
  status?: TeacherLeaveStatus
  start_at?: string
  end_at?: string
  keyword?: string
}

export interface TeacherLeaveCard {
  id: number
  leave_no: string
  leave_type: string
  lesson_id: number
  lesson_student_id: number
  student_name_snapshot?: string | null
  reason: string
  status: TeacherLeaveStatus
  requested_at?: string | null
  lesson_title?: string | null
  lesson_start_at?: string | null
  lesson_end_at?: string | null
}

export interface TeacherLeaveDetail extends TeacherLeaveCard {
  source: string
  class_id: number
  course_id: number
  student_id: number
  teacher_id?: number | null
  reviewed_at?: string | null
  reviewed_by?: number | null
  review_remark?: string | null
  makeup_required: boolean
  makeup_lesson_id?: number | null
  created_at?: string | null
}

export interface TeacherLeaveReviewPayload extends TeacherCampusQuery {
  review_remark: string
}

export type TeacherLeaveReviewResult = TeacherLeaveDetail

interface MineAdminResult<T> {
  code: number
  message: string
  data: T
}

export function getTeacherTodayLessons(params: TeacherTodayLessonParams): Promise<TeacherTodayLessonResult> {
  return requestTeacher('/mobile/education/academic/teacher/lessons/today', 'GET', params)
}

export function pageTeacherLessons(params: TeacherLessonPageParams): Promise<PageResult<TeacherLessonCard>> {
  return requestTeacher('/mobile/education/academic/teacher/lessons/page', 'GET', params)
}

export function getTeacherLessonDetail(lessonId: number, params?: TeacherCampusQuery): Promise<TeacherLessonDetail> {
  return requestTeacher(`/mobile/education/academic/teacher/lessons/${lessonId}`, 'GET', params)
}

export function getTeacherAttendanceSheet(lessonId: number, params?: TeacherCampusQuery): Promise<TeacherAttendanceSheet> {
  return requestTeacher(`/mobile/education/academic/teacher/lessons/${lessonId}/attendance-sheet`, 'GET', params)
}

export function submitTeacherAttendance(lessonId: number, payload: TeacherAttendanceSubmitPayload): Promise<TeacherAttendanceResult> {
  return requestTeacher(`/mobile/education/academic/teacher/lessons/${lessonId}/attendance`, 'POST', payload)
}

export function getTeacherAttendanceResult(lessonId: number, params?: TeacherCampusQuery): Promise<TeacherAttendanceResult> {
  return requestTeacher(`/mobile/education/academic/teacher/lessons/${lessonId}/attendance-result`, 'GET', params)
}

export function pageTeacherLeaveRequests(params: TeacherLeavePageParams): Promise<PageResult<TeacherLeaveCard>> {
  return requestTeacher('/mobile/education/academic/teacher/leave-requests/page', 'GET', params)
}

export function getTeacherLeaveRequest(id: number, params?: TeacherCampusQuery): Promise<TeacherLeaveDetail> {
  return requestTeacher(`/mobile/education/academic/teacher/leave-requests/${id}`, 'GET', params)
}

export function approveTeacherLeaveRequest(id: number, payload: TeacherLeaveReviewPayload): Promise<TeacherLeaveReviewResult> {
  return requestTeacher(`/mobile/education/academic/teacher/leave-requests/${id}/approve`, 'PUT', payload)
}

export function rejectTeacherLeaveRequest(id: number, payload: TeacherLeaveReviewPayload): Promise<TeacherLeaveReviewResult> {
  return requestTeacher(`/mobile/education/academic/teacher/leave-requests/${id}/reject`, 'PUT', payload)
}

function requestTeacher<T>(url: string, method: UniApp.RequestOptions['method'], data?: object): Promise<T> {
  return new Promise((resolve, reject) => {
    uni.request({
      url,
      method,
      data: compact(data),
      header: requestHeaders(),
      success: (response) => {
        const result = response.data as MineAdminResult<T>
        if (result?.code === 200) {
          resolve(result.data)
          return
        }

        const error = new MobileApiError(result?.message || 'Request failed')
        error.code = result?.code
        error.data = result?.data
        reject(error)
      },
      fail: (error) => {
        reject(new Error(error.errMsg || 'Network request failed'))
      },
    })
  })
}

function compact(params?: object): Record<string, unknown> {
  return Object.fromEntries(
    Object.entries(params || {}).filter(([, value]) => value !== undefined && value !== null && value !== '')
  )
}

function requestHeaders(): Record<string, string> {
  const token = storageToken()

  return token === null ? {} : { Authorization: `Bearer ${token}` }
}

function storageToken(): string | null {
  try {
    return uni.getStorageSync('access_token') || uni.getStorageSync('token') || null
  } catch {
    return null
  }
}
