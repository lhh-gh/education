import type { MineResult } from '../foundation/types.ts'
import type { AdmissionPage, AdmissionScopedParams } from './types.ts'
import { admissionGetOptions, admissionRequestOptions } from './types.ts'

export interface TrialLessonRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  lead_id: number
  lead_student_id: number
  course_id: number
  teacher_id: number
  classroom_id?: number | null
  start_time: string
  end_time: string
  status: string
}

export function pageTrialLessons(params: AdmissionScopedParams & { date?: string, teacher_id?: number, status?: string }): Promise<MineResult<AdmissionPage<TrialLessonRecord>>> {
  return useHttp().get('/admin/education/admissions/trial-lessons/page', admissionGetOptions(params))
}

export function createTrialLesson(payload: Partial<TrialLessonRecord> & { tenant_id?: number, campus_id?: number }): Promise<MineResult<TrialLessonRecord>> {
  return useHttp().post('/admin/education/admissions/trial-lessons', payload, admissionRequestOptions(payload))
}

export function updateTrialLesson(payload: Partial<TrialLessonRecord> & { id: number, tenant_id?: number, campus_id?: number }): Promise<MineResult<TrialLessonRecord>> {
  return createTrialLesson(payload)
}

export function cancelTrialLesson(id: number, payload: { tenant_id?: number, campus_id?: number }): Promise<MineResult<TrialLessonRecord>> {
  return useHttp().post(`/admin/education/admissions/trial-lessons/${id}/attendance`, { attendance_status: 'cancelled' }, admissionRequestOptions(payload))
}

export function saveTrialAttendance(id: number, payload: { tenant_id?: number, campus_id?: number, lead_student_id: number, attendance_status: string, remark?: string }): Promise<MineResult<Record<string, unknown>>> {
  return useHttp().post(`/admin/education/admissions/trial-lessons/${id}/attendance`, payload, admissionRequestOptions(payload))
}

export function saveTrialFeedback(payload: { tenant_id?: number, campus_id?: number, trial_lesson_id: number, feedback_type: string, score?: number, content: string }): Promise<MineResult<Record<string, unknown>>> {
  return useHttp().post('/admin/education/admissions/trial-feedbacks', payload, admissionRequestOptions(payload))
}
