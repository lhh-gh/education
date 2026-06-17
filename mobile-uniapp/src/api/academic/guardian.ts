import { MobileApiError } from '../foundation/context'

export { MobileApiError }

export type GuardianLessonStatus = 'scheduled' | 'cancelled' | 'completed'
export type GuardianAccountStatus = 'active' | 'frozen' | 'closed'
export type GuardianConsumptionStatus = 'active' | 'reversed'
export type GuardianNoticeStatus = 'unread' | 'read'
export type GuardianLeaveType = 'sick' | 'personal' | 'school' | 'other'
export type GuardianNoticeType = 'academic' | 'activity' | 'fee' | 'system'

export interface PageResult<T> {
  list: T[]
  total: number
  page: number
  pageSize: number
}

export interface GuardianStudentParams {
  client_type?: 'wechat_service' | 'wechat_miniprogram' | 'h5'
}

export interface GuardianStudentRecord {
  id: number
  student_no: string
  name: string
  gender?: string | null
  campus_id?: number | null
  campus_name?: string | null
  relation: string
  is_primary: boolean
  can_receive_notice: boolean
  can_submit_leave: boolean
  status: string
}

export type GuardianStudentListResult = PageResult<GuardianStudentRecord>

export interface GuardianLessonPageParams {
  page?: number
  pageSize?: number
  start_at: string
  end_at: string
  status?: GuardianLessonStatus
}

export interface GuardianLessonRecord {
  lesson_id: number
  lesson_student_id: number
  class_id?: number | null
  class_name_snapshot?: string | null
  course_id?: number | null
  course_name_snapshot?: string | null
  teacher_name_snapshot?: string | null
  classroom_name_snapshot?: string | null
  title: string
  start_at: string
  end_at: string
  lesson_units?: string
  lesson_status: GuardianLessonStatus
  lesson_student_status: string
}

export interface GuardianAccountPageParams {
  page?: number
  pageSize?: number
  status?: GuardianAccountStatus
}

export interface GuardianAccountRecord {
  id: number
  campus_id: number
  student_id: number
  course_id: number
  course_name: string
  purchased_units: string
  bonus_units: string
  consumed_units: string
  adjusted_units: string
  refunded_units: string
  frozen_units: string
  available_units: string
  status: GuardianAccountStatus
  opened_at?: string | null
  expires_at?: string | null
}

export interface GuardianConsumptionPageParams {
  page?: number
  pageSize?: number
  account_id?: number
  source_type?: 'attendance' | 'rollback'
  status?: GuardianConsumptionStatus
  start_at?: string
  end_at?: string
}

export interface GuardianConsumptionRecord {
  id: number
  consumption_no: string
  account_id: number
  student_id: number
  course_id: number
  course_name: string
  lesson_id?: number | null
  lesson_title?: string | null
  lesson_start_at?: string | null
  source_type: string
  direction: string
  units: string
  before_available_units: string
  after_available_units: string
  status: GuardianConsumptionStatus
  created_at: string
}

export interface GuardianNoticePageParams {
  page?: number
  pageSize?: number
  status?: GuardianNoticeStatus | 'all'
  notice_type?: GuardianNoticeType
}

export interface GuardianNoticeCard {
  receipt_id: number
  notice_id: number
  title: string
  notice_type: GuardianNoticeType
  priority: 'normal' | 'important' | 'urgent'
  student_name_snapshot: string
  status: GuardianNoticeStatus
  published_at?: string | null
}

export interface GuardianNoticeDetail extends GuardianNoticeCard {
  content: string
  read_at?: string | null
}

export interface GuardianNoticeReadResult {
  receipt_id: number
  status: GuardianNoticeStatus
  read_at?: string | null
}

export interface GuardianLeaveCreatePayload {
  lesson_student_id: number
  leave_type: GuardianLeaveType
  reason: string
  makeup_required?: boolean
}

export interface GuardianLeaveRecord {
  id: number
  leave_no: string
  source: 'guardian'
  leave_type: GuardianLeaveType
  lesson_id: number
  lesson_student_id: number
  student_id: number
  student_name?: string | null
  course_id?: number | null
  reason: string
  status: 'pending' | string
  requested_at?: string | null
  makeup_required: boolean
  reviewed_at?: string | null
  review_remark?: string | null
}

interface MineAdminResult<T> {
  code: number
  message: string
  data: T
}

export function getGuardianStudents(params?: GuardianStudentParams): Promise<GuardianStudentListResult> {
  return requestGuardian('/mobile/education/academic/guardian/students', 'GET', params)
}

export function pageGuardianStudentLessons(
  studentId: number,
  params: GuardianLessonPageParams,
): Promise<PageResult<GuardianLessonRecord>> {
  return requestGuardian(`/mobile/education/academic/guardian/students/${studentId}/lessons`, 'GET', params)
}

export function pageGuardianStudentAccounts(
  studentId: number,
  params: GuardianAccountPageParams,
): Promise<PageResult<GuardianAccountRecord>> {
  return requestGuardian(`/mobile/education/academic/guardian/students/${studentId}/accounts`, 'GET', params)
}

export function pageGuardianStudentConsumptions(
  studentId: number,
  params: GuardianConsumptionPageParams,
): Promise<PageResult<GuardianConsumptionRecord>> {
  return requestGuardian(`/mobile/education/academic/guardian/students/${studentId}/consumptions`, 'GET', params)
}

export function pageGuardianNotices(params: GuardianNoticePageParams): Promise<PageResult<GuardianNoticeCard>> {
  return requestGuardian('/mobile/education/academic/guardian/notices/page', 'GET', params)
}

export function getGuardianNotice(receiptId: number): Promise<GuardianNoticeDetail> {
  return requestGuardian(`/mobile/education/academic/guardian/notices/${receiptId}`, 'GET')
}

export function readGuardianNotice(receiptId: number): Promise<GuardianNoticeReadResult> {
  return requestGuardian(`/mobile/education/academic/guardian/notices/${receiptId}/read`, 'PUT')
}

export function createGuardianLeave(payload: GuardianLeaveCreatePayload): Promise<GuardianLeaveRecord> {
  return requestGuardian('/mobile/education/academic/guardian/leave-requests', 'POST', payload)
}

function requestGuardian<T>(url: string, method: UniApp.RequestOptions['method'], data?: object): Promise<T> {
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
