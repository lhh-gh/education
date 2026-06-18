import type { MineResult } from '../foundation/types.ts'
import type { ContentPage, ContentScopedParams, MaterialRelationRow } from './types.ts'
import { contentGetOptions, contentRequestOptions } from './types.ts'

export interface MaterialRelationPayload extends ContentScopedParams {
  relations: Array<{ target_type: string, target_id: number, relation_note?: string }>
}

export function pageMaterialRelations(params: ContentScopedParams): Promise<MineResult<ContentPage<MaterialRelationRow>>> {
  return useHttp().get('/admin/education/content/material-relations', contentGetOptions(params))
}

export function saveMaterialRelations(materialId: number, payload: MaterialRelationPayload): Promise<MineResult<{ material_id: number, status: string }>> {
  return useHttp().post(`/admin/education/content/materials/${materialId}/relations`, payload, contentRequestOptions(payload))
}
