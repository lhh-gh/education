import type { MineResult } from '../foundation/types.ts'
import type { OperationPage, OperationScopedParams } from './types.ts'
import { operationGetOptions, operationRequestOptions } from './types.ts'

export type MakeupEntitlementStatus = 'available' | 'arranged' | 'used' | 'expired' | 'cancelled'
export type MakeupRecordStatus = 'arranged' | 'completed' | 'cancelled'

export interface MakeupEntitlementRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  student_id: number
  course_id: number
  source_lesson_id: number
  source_leave_request_id: number
  status: MakeupEntitlementStatus
  expires_at?: string | null
  course_name?: string | null
  student_name?: string | null
  created_at?: string | null
}

export interface MakeupRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  student_id: number
  makeup_entitlement_id: number
  makeup_lesson_id: number
  status: MakeupRecordStatus
  arranged_at?: string | null
  completed_at?: string | null
  cancelled_at?: string | null
}

export interface MakeupEntitlementPageParams extends OperationScopedParams {
  student_id?: number
  course_id?: number
  status?: MakeupEntitlementStatus
  expires_start_at?: string
  expires_end_at?: string
}

export interface MakeupArrangePayload {
  tenant_id?: number
  campus_id?: number
  makeup_lesson_id: number
  arranged_at: string
}

export function pageMakeupEntitlements(params: MakeupEntitlementPageParams): Promise<MineResult<OperationPage<MakeupEntitlementRecord>>> {
  return useHttp().get('/admin/education/operations/makeup-entitlements/page', operationGetOptions(params))
}

export function arrangeMakeup(id: number, payload: MakeupArrangePayload): Promise<MineResult<{ makeup_record_id: number, status: MakeupRecordStatus }>> {
  return useHttp().post(`/admin/education/operations/makeup-entitlements/${id}/arrange`, payload, operationRequestOptions(payload))
}

export function cancelMakeupRecord(id: number, payload: { tenant_id?: number, campus_id?: number, reason?: string } = {}): Promise<MineResult<MakeupRecord>> {
  return useHttp().post(`/admin/education/operations/makeup-records/${id}/cancel`, payload, operationRequestOptions(payload))
}

export function pageMakeupRecords(params: OperationScopedParams & { student_id?: number, status?: MakeupRecordStatus }): Promise<MineResult<OperationPage<MakeupRecord>>> {
  return useHttp().get('/admin/education/operations/makeup-records/page', operationGetOptions(params))
}
