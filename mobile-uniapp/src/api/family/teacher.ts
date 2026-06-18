import type { OperationScopedParams, PageResult } from '../operations/shared'
import { requestOperation } from '../operations/shared'

export { MobileApiError } from '../operations/shared'

export interface TeacherLessonCommentPayload extends OperationScopedParams {
  lesson_id: number
  student_id: number
  content: string
  tag_ids?: number[]
  publish?: boolean
}

export interface TeacherLessonCommentResult {
  lesson_comment_id: number
  status: 'draft' | 'published' | 'withdrawn'
}

export interface TeacherCommentTemplate {
  id: number
  template_name: string
  content: string
}

export interface TeacherPerformanceTag {
  id: number
  tag_name: string
  tag_type: string
}

export interface TeacherHomeworkReviewItem {
  homework_submission_id: number
  student_id: number
  student_name?: string | null
  title?: string | null
  content?: string | null
  status: string
}

export interface TeacherHomeworkSubmissionParams extends OperationScopedParams {
  status?: string
}

export interface TeacherHomeworkReviewPayload extends OperationScopedParams {
  homework_submission_id: number
  score?: number
  content: string
}

export interface TeacherHomeworkReviewResult {
  homework_review_id: number
  target_status: string
}

export interface TeacherGrowthRecordPayload extends OperationScopedParams {
  student_id: number
  record_type: string
  title: string
  content: string
  publish?: boolean
}

export interface TeacherGrowthRecordResult {
  growth_record_id: number
  status: 'draft' | 'published' | 'withdrawn'
}

export interface TeacherFamilyMessage {
  id: number
  thread_id: string
  student_id: number
  sender_type: string
  content: string
  status: string
  created_at?: string | null
}

export interface TeacherFamilyMessagePayload extends OperationScopedParams {
  student_id: number
  thread_id?: string
  content: string
}

export function createTeacherLessonComment(payload: TeacherLessonCommentPayload): Promise<TeacherLessonCommentResult> {
  return requestOperation('/mobile/education/family/teacher/lesson-comments', 'POST', payload)
}

export function getTeacherCommentTemplates(params?: OperationScopedParams): Promise<PageResult<TeacherCommentTemplate>> {
  return requestOperation('/mobile/education/family/teacher/comment-templates', 'GET', params)
}

export function getTeacherPerformanceTags(params?: OperationScopedParams): Promise<PageResult<TeacherPerformanceTag>> {
  return requestOperation('/mobile/education/family/teacher/performance-tags', 'GET', params)
}

export function getTeacherHomeworkSubmissions(params?: TeacherHomeworkSubmissionParams): Promise<PageResult<TeacherHomeworkReviewItem>> {
  return requestOperation('/mobile/education/family/teacher/homework-submissions', 'GET', params)
}

export function reviewTeacherHomework(payload: TeacherHomeworkReviewPayload): Promise<TeacherHomeworkReviewResult> {
  return requestOperation('/mobile/education/family/teacher/homework-reviews', 'POST', payload)
}

export function saveTeacherGrowthRecord(payload: TeacherGrowthRecordPayload): Promise<TeacherGrowthRecordResult> {
  return requestOperation('/mobile/education/family/teacher/growth-records', 'POST', payload)
}

export function getTeacherFamilyMessages(params?: OperationScopedParams): Promise<PageResult<TeacherFamilyMessage>> {
  return requestOperation('/mobile/education/family/messages', 'GET', params)
}

export function sendTeacherFamilyMessage(payload: TeacherFamilyMessagePayload): Promise<{ message_id: number, status: string }> {
  return requestOperation('/mobile/education/family/messages', 'POST', payload)
}
