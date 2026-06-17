import type { OperationScopedParams } from '../operations/shared'
import { requestOperation } from '../operations/shared'

export { MobileApiError } from '../operations/shared'

export type TeacherTrialLessonStatus = 'scheduled' | 'attended' | 'absent' | 'cancelled' | 'converted'

export interface TeacherTrialLessonParams extends OperationScopedParams {
  date?: string
}

export interface TeacherTrialLesson {
  id: number
  lead_id: number
  lead_student_id?: number
  student_name?: string
  course_id?: number
  teacher_id?: number
  classroom_id?: number | null
  start_time: string
  end_time?: string
  status: TeacherTrialLessonStatus | string
  remark?: string | null
}

export interface TeacherTrialLessonResult {
  list: TeacherTrialLesson[]
}

export interface TeacherTrialFeedbackPayload extends OperationScopedParams {
  trial_lesson_id: number
  score?: number
  content: string
  recommend_course_id?: number
}

export interface TeacherTrialFeedbackResult {
  id: number
  trial_lesson_id?: number
  feedback_type?: 'teacher'
}

export function getTeacherTrialLessons(params?: TeacherTrialLessonParams): Promise<TeacherTrialLessonResult> {
  return requestOperation('/mobile/education/admissions/teacher/trial-lessons', 'GET', params)
}

export function submitTeacherTrialFeedback(payload: TeacherTrialFeedbackPayload): Promise<TeacherTrialFeedbackResult> {
  return requestOperation('/mobile/education/admissions/teacher/trial-feedbacks', 'POST', payload)
}
