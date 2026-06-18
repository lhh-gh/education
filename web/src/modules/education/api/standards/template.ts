import type { MineResult } from '../foundation/types.ts'
import type { StandardsScopedParams } from './types.ts'
import { standardsGetOptions, standardsRequestOptions } from './types.ts'

export interface ServiceTemplatePayload extends StandardsScopedParams {
  template_set_code: string
  template_set_name: string
  course_id?: number
  items?: Array<{ item_type: string, item_title: string, item_content: string, sort_order?: number }>
}

export function pageServiceTemplateSets(params: StandardsScopedParams): Promise<MineResult<any>> {
  return useHttp().get('/admin/education/standards/service-templates', standardsGetOptions(params))
}

export function saveServiceTemplateSet(payload: ServiceTemplatePayload): Promise<MineResult<{ template_set_id: number }>> {
  return useHttp().post('/admin/education/standards/service-templates', payload, standardsRequestOptions(payload))
}

export function saveServiceTemplateItems(payload: ServiceTemplatePayload): Promise<MineResult<{ template_set_id: number }>> {
  return saveServiceTemplateSet(payload)
}
