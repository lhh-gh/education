import type { MineResult } from '../foundation/types.ts'
import type { ContentPage, ContentScopedParams, MaterialVersionRow } from './types.ts'
import { contentGetOptions, contentRequestOptions } from './types.ts'

export interface MaterialVersionPayload extends ContentScopedParams {
  title: string
  content?: string
  snapshot_json?: Record<string, unknown>
}

export function pageMaterialVersions(params: ContentScopedParams): Promise<MineResult<ContentPage<MaterialVersionRow>>> {
  return useHttp().get('/admin/education/content/material-versions', contentGetOptions(params))
}

export function createMaterialVersion(materialId: number, payload: MaterialVersionPayload): Promise<MineResult<{ material_version_id: number }>> {
  return useHttp().post(`/admin/education/content/materials/${materialId}/versions`, payload, contentRequestOptions(payload))
}

export function getMaterialVersionDetail(id: number, params: ContentScopedParams = {}): Promise<MineResult<MaterialVersionRow>> {
  return useHttp().get(`/admin/education/content/material-versions/${id}`, contentGetOptions(params))
}
