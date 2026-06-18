import type { MineResult } from '../foundation/types.ts'
import type { StandardsScopedParams } from './types.ts'
import { standardsGetOptions, standardsRequestOptions } from './types.ts'

export interface CourseMaterialPayload extends StandardsScopedParams {
  material_code: string
  material_name: string
  course_id?: number
  material_type: string
  file_url?: string
  guardian_visible?: boolean
}

export function pageCourseMaterials(params: StandardsScopedParams): Promise<MineResult<any>> {
  return useHttp().get('/admin/education/standards/materials', standardsGetOptions(params))
}

export function saveCourseMaterial(payload: CourseMaterialPayload): Promise<MineResult<{ material_id: number }>> {
  return useHttp().post('/admin/education/standards/materials', payload, standardsRequestOptions(payload))
}
