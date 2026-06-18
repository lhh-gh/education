import type { MineResult } from '../foundation/types.ts'
import type { ContentPage, ContentScopedParams, ShowcaseRow } from './types.ts'
import { contentGetOptions, contentRequestOptions } from './types.ts'

export interface ShowcasePayload extends ContentScopedParams {
  showcase_id?: number
  student_id: number
  stage_goal_id?: number
  title: string
  summary?: string
  items?: Array<{ item_type: string, title: string, content?: string, student_work_id?: number, material_id?: number }>
}

export function pageShowcases(params: ContentScopedParams): Promise<MineResult<ContentPage<ShowcaseRow>>> {
  return useHttp().get('/admin/education/content/showcases', contentGetOptions(params))
}

export function saveShowcase(payload: ShowcasePayload): Promise<MineResult<{ showcase_id: number, status: string }>> {
  return useHttp().post('/admin/education/content/showcases', payload, contentRequestOptions(payload))
}

export function publishShowcase(id: number, payload: ContentScopedParams = {}): Promise<MineResult<{ showcase_id: number, status: string }>> {
  return useHttp().post(`/admin/education/content/showcases/${id}/publish`, payload, contentRequestOptions(payload))
}

export function withdrawShowcase(id: number, payload: ContentScopedParams = {}): Promise<MineResult<{ showcase_id: number, status: string }>> {
  return useHttp().post(`/admin/education/content/showcases/${id}/withdraw`, payload, contentRequestOptions(payload))
}
