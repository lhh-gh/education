import type { MineResult } from '../foundation/types.ts'
import type { StandardsScopedParams } from './types.ts'
import { standardsGetOptions, standardsRequestOptions } from './types.ts'

export interface DeliveryStandardPayload extends StandardsScopedParams {
  course_id: number
  standard_code: string
  standard_name: string
  lesson_type: string
  content: string
}

export function pageDeliveryStandards(params: StandardsScopedParams): Promise<MineResult<any>> {
  return useHttp().get('/admin/education/standards/delivery-standards', standardsGetOptions(params))
}

export function saveDeliveryStandard(payload: DeliveryStandardPayload): Promise<MineResult<{ delivery_standard_id: number }>> {
  return useHttp().post('/admin/education/standards/delivery-standards', payload, standardsRequestOptions(payload))
}
