import type { OperationScopedParams } from '../operations/shared'
import { requestOperation } from '../operations/shared'

export { MobileApiError } from '../operations/shared'

export type TeacherGrowthTrialLessonStatus = 'scheduled' | 'attended' | 'absent' | 'cancelled' | 'converted'

export interface TeacherGrowthTrialLessonParams extends OperationScopedParams {
  date?: string
}

export interface TeacherGrowthTrialLesson {
  id: number
  lead_id: number
  lead_student_id?: number
  student_name?: string
  course_id?: number
  teacher_id?: number
  classroom_id?: number | null
  start_time: string
  end_time?: string
  status: TeacherGrowthTrialLessonStatus | string
  remark?: string | null
}

export interface TeacherGrowthTrialLessonResult {
  list: TeacherGrowthTrialLesson[]
}

export interface TeacherGrowthTrialFeedbackPayload extends OperationScopedParams {
  trial_lesson_id: number
  classroom_performance: string
  course_recommendation: string
  teacher_note: string
}

export interface TeacherGrowthTrialFeedbackResult {
  trial_feedback_id: number
}

export function getTeacherGrowthTrialLessons(params?: TeacherGrowthTrialLessonParams): Promise<TeacherGrowthTrialLessonResult> {
  return requestOperation('/mobile/education/admissions/teacher/trial-lessons', 'GET', params)
}

export function submitTeacherGrowthTrialFeedback(payload: TeacherGrowthTrialFeedbackPayload): Promise<TeacherGrowthTrialFeedbackResult> {
  return requestOperation('/mobile/education/growth/teacher/trial-feedback', 'POST', payload)
}
