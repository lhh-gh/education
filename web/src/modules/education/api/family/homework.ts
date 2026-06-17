import type { MineResult } from '../foundation/types.ts'
import type { FamilyPage, FamilyScopedParams } from './types.ts'
import { familyGetOptions, familyRequestOptions } from './types.ts'

export interface HomeworkAssignmentRecord {
  id: number
  title: string
  content: string
  status: string
  target_count?: number
  submitted_count?: number
  reviewed_count?: number
  due_at?: string
}

export interface HomeworkAssignmentPayload extends FamilyScopedParams {
  title: string
  content: string
  course_id?: number
  class_id?: number
  lesson_id?: number
  due_at?: string
  student_ids: number[]
}

export interface HomeworkSubmissionRecord {
  id: number
  homework_target_id: number
  student_id: number
  status: string
  submitted_at?: string
}

export function pageHomeworkAssignments(params: FamilyScopedParams): Promise<MineResult<FamilyPage<HomeworkAssignmentRecord>>> {
  return useHttp().get('/admin/education/family/homework-assignments/page', familyGetOptions(params))
}

export function saveHomeworkAssignment(payload: HomeworkAssignmentPayload): Promise<MineResult<{ homework_assignment_id: number, target_count: number, status: string }>> {
  return useHttp().post('/admin/education/family/homework-assignments', payload, familyRequestOptions(payload))
}

export function publishHomeworkAssignment(id: number, params: FamilyScopedParams = {}): Promise<MineResult<{ homework_assignment_id: number, status: string }>> {
  return useHttp().post(`/admin/education/family/homework-assignments/${id}/publish`, {}, familyRequestOptions(params))
}

export function pageHomeworkSubmissions(params: FamilyScopedParams): Promise<MineResult<FamilyPage<HomeworkSubmissionRecord>>> {
  return useHttp().get('/admin/education/family/homework-submissions/page', familyGetOptions(params))
}
