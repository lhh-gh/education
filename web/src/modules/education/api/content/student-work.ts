import type { MineResult } from '../foundation/types.ts'
import type { ContentPage, ContentScopedParams, StudentWorkRow } from './types.ts'
import { contentGetOptions, contentRequestOptions } from './types.ts'

export function pageStudentWorks(params: ContentScopedParams): Promise<MineResult<ContentPage<StudentWorkRow>>> {
  return useHttp().get('/admin/education/content/student-works', contentGetOptions(params))
}

export function publishStudentWork(id: number, payload: ContentScopedParams = {}): Promise<MineResult<{ student_work_id: number, status: string }>> {
  return useHttp().post(`/admin/education/content/student-works/${id}/publish`, payload, contentRequestOptions(payload))
}

export function withdrawStudentWork(id: number, payload: ContentScopedParams = {}): Promise<MineResult<{ student_work_id: number, status: string }>> {
  return useHttp().post(`/admin/education/content/student-works/${id}/withdraw`, payload, contentRequestOptions(payload))
}
