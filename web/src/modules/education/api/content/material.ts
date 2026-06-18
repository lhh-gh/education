import type { MineResult } from '../foundation/types.ts'
import type { ContentPage, ContentScopedParams, LearningMaterialRow } from './types.ts'
import { contentGetOptions, contentRequestOptions } from './types.ts'

export interface LearningMaterialPayload extends ContentScopedParams {
  material_id?: number
  material_code: string
  material_name: string
  course_id?: number
  material_type: string
  guardian_visible?: boolean
  summary?: string
}

export interface MaterialPublishPayload extends ContentScopedParams {
  publish_note?: string
}

export function pageLearningMaterials(params: ContentScopedParams): Promise<MineResult<ContentPage<LearningMaterialRow>>> {
  return useHttp().get('/admin/education/content/materials', contentGetOptions(params))
}

export function saveLearningMaterial(payload: LearningMaterialPayload): Promise<MineResult<{ material_id: number, status: string }>> {
  return useHttp().post('/admin/education/content/materials', payload, contentRequestOptions(payload))
}

export function publishLearningMaterial(id: number, payload: MaterialPublishPayload = {}): Promise<MineResult<{ material_id: number, status: string }>> {
  return useHttp().post(`/admin/education/content/materials/${id}/publish`, payload, contentRequestOptions(payload))
}

export function withdrawLearningMaterial(id: number, payload: ContentScopedParams = {}): Promise<MineResult<{ material_id: number, status: string }>> {
  return useHttp().post(`/admin/education/content/materials/${id}/withdraw`, payload, contentRequestOptions(payload))
}
