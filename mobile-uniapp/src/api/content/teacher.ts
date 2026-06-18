import type { OperationScopedParams, PageResult } from '../operations/shared'
import { requestOperation } from '../operations/shared'

export { MobileApiError } from '../operations/shared'

export interface TeacherContentParams extends OperationScopedParams {
  course_id?: number
  keyword?: string
  status?: string
}

export interface TeacherMaterial {
  material_id: number
  id?: number
  material_name: string
  material_type?: string
  course_id?: number
  status: string
  favorite?: boolean
}

export interface TeacherFavoritePayload extends OperationScopedParams {
  material_id: number
  favorited: boolean
}

export interface TeacherLessonMaterialUsagePayload extends OperationScopedParams {
  lesson_id: number
  material_id: number
  material_version_id?: number
  usage_type: string
  remark?: string
}

export interface TeacherStudentWorkPayload extends OperationScopedParams {
  student_id: number
  lesson_id?: number
  stage_goal_id?: number
  title: string
  description?: string
  attachment_ids?: number[]
}

export function getTeacherMaterials(params?: TeacherContentParams): Promise<PageResult<TeacherMaterial>> {
  return requestOperation('/mobile/education/content/teacher/materials', 'GET', params)
}

export function saveTeacherMaterialFavorite(payload: TeacherFavoritePayload): Promise<{ status: string }> {
  return requestOperation('/mobile/education/content/teacher/material-favorites', 'POST', payload)
}

export function createTeacherLessonMaterialUsage(payload: TeacherLessonMaterialUsagePayload): Promise<{ lesson_material_usage_id: number }> {
  return requestOperation('/mobile/education/content/teacher/lesson-material-usages', 'POST', payload)
}

export function saveTeacherStudentWork(payload: TeacherStudentWorkPayload): Promise<{ student_work_id: number, status: string }> {
  return requestOperation('/mobile/education/content/teacher/student-works', 'POST', payload)
}
