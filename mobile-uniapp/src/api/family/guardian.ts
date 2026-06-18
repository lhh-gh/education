import type { OperationScopedParams, PageResult } from '../operations/shared'
import { requestOperation, selectedStudentId } from '../operations/shared'

export { MobileApiError } from '../operations/shared'

export interface GuardianFamilyParams extends OperationScopedParams {
  status?: string
  business_type?: string
  business_id?: number
}

export interface GuardianLessonComment {
  id: number
  lesson_id: number
  student_id: number
  teacher_id: number
  content: string
  status: 'published' | string
  published_at?: string | null
}

export interface GuardianHomeworkTarget {
  homework_target_id: number
  homework_assignment_id: number
  student_id: number
  title: string
  content?: string | null
  status: 'assigned' | 'submitted' | 'reviewed' | 'overdue' | string
  due_at?: string | null
}

export interface GuardianHomeworkSubmissionPayload extends OperationScopedParams {
  homework_target_id: number
  student_id?: number
  content?: string
  attachment_ids?: number[]
}

export interface GuardianHomeworkSubmissionResult {
  homework_submission_id: number
  status: string
}

export interface GuardianLearningReport {
  learning_report_id: number
  student_id: number
  report_title: string
  report_period: string
  summary?: string | null
  status: 'published' | string
}

export interface GuardianGrowthRecord {
  id: number
  student_id: number
  record_type: string
  title: string
  content: string
  status: 'published' | string
}

export interface GuardianFamilyMessage {
  id: number
  thread_id: string
  student_id: number
  sender_type: string
  content: string
  status: string
  created_at?: string | null
}

export interface GuardianFamilyMessagePayload extends OperationScopedParams {
  student_id?: number
  thread_id?: string
  content: string
}

export interface GuardianReadReceiptPayload extends OperationScopedParams {
  business_type: 'lesson_comment' | 'learning_report' | 'growth_record'
  business_id: number
  student_id?: number
}

export function getGuardianLessonComments(params?: GuardianFamilyParams): Promise<PageResult<GuardianLessonComment>> {
  const studentId = requireStudentId(params)

  return requestOperation(`/mobile/education/family/guardian/students/${studentId}/lesson-comments`, 'GET', withoutStudent(params))
}

export function getGuardianHomework(params?: GuardianFamilyParams): Promise<PageResult<GuardianHomeworkTarget>> {
  return requestOperation('/mobile/education/family/guardian/homework', 'GET', withSelectedStudent(params))
}

export function submitGuardianHomework(payload: GuardianHomeworkSubmissionPayload): Promise<GuardianHomeworkSubmissionResult> {
  return requestOperation('/mobile/education/family/guardian/homework-submissions', 'POST', withSelectedStudent(payload))
}

export function getGuardianLearningReports(params?: GuardianFamilyParams): Promise<PageResult<GuardianLearningReport>> {
  const studentId = requireStudentId(params)

  return requestOperation(`/mobile/education/family/guardian/students/${studentId}/learning-reports`, 'GET', withoutStudent(params))
}

export function getGuardianGrowthRecords(params?: GuardianFamilyParams): Promise<PageResult<GuardianGrowthRecord>> {
  const studentId = requireStudentId(params)

  return requestOperation(`/mobile/education/family/guardian/students/${studentId}/growth-records`, 'GET', withoutStudent(params))
}

export function getGuardianFamilyMessages(params?: GuardianFamilyParams): Promise<PageResult<GuardianFamilyMessage>> {
  return requestOperation('/mobile/education/family/messages', 'GET', withSelectedStudent(params))
}

export function sendGuardianFamilyMessage(payload: GuardianFamilyMessagePayload): Promise<{ message_id: number, status: string }> {
  return requestOperation('/mobile/education/family/messages', 'POST', withSelectedStudent(payload))
}

export function markGuardianFamilyRead(payload: GuardianReadReceiptPayload): Promise<{ read_receipt_id: number }> {
  return requestOperation('/mobile/education/family/guardian/read-receipts', 'POST', withSelectedStudent(payload))
}

function withSelectedStudent<T extends OperationScopedParams>(params?: T): T & { student_id?: number } {
  return { ...(params || {} as T), student_id: selectedStudentId(params) }
}

function withoutStudent<T extends OperationScopedParams>(params?: T): Omit<T, 'student_id'> {
  const { student_id: _studentId, ...rest } = params || {} as T

  return rest
}

function requireStudentId(params?: OperationScopedParams): number {
  return selectedStudentId(params) || 0
}
