import type { MineResult } from '../foundation/types.ts'
import type { OperationPage, OperationScopedParams } from './types.ts'
import { operationGetOptions, operationRequestOptions } from './types.ts'

export type ConsumptionReviewStatus = 'pending' | 'approved' | 'rejected'

export interface ConsumptionReviewRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  lesson_id: number
  teacher_id?: number | null
  status: ConsumptionReviewStatus
  review_note?: string | null
  reviewed_by?: number | null
  reviewed_at?: string | null
  submitted_at?: string | null
  consumption_ids?: number[]
}

export interface ConsumptionAdjustmentRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  original_consumption_id: number
  adjustment_consumption_id?: number | null
  credits: string
  reason: string
  created_at?: string | null
}

export interface ConsumptionReviewPageParams extends OperationScopedParams {
  lesson_id?: number
  teacher_id?: number
  status?: ConsumptionReviewStatus
}

export interface ConsumptionReviewActionPayload {
  tenant_id?: number
  campus_id?: number
  review_note?: string
}

export interface ConsumptionAdjustmentPayload {
  tenant_id?: number
  campus_id?: number
  credits: string
  reason: string
}

export function pageConsumptionReviews(params: ConsumptionReviewPageParams): Promise<MineResult<OperationPage<ConsumptionReviewRecord>>> {
  return useHttp().get('/admin/education/operations/consumption-reviews/page', operationGetOptions(params))
}

export function approveConsumptionReview(id: number, payload: ConsumptionReviewActionPayload): Promise<MineResult<ConsumptionReviewRecord>> {
  return useHttp().post(`/admin/education/operations/consumption-reviews/${id}/approve`, payload, operationRequestOptions(payload))
}

export function rejectConsumptionReview(id: number, payload: ConsumptionReviewActionPayload): Promise<MineResult<ConsumptionReviewRecord>> {
  return useHttp().post(`/admin/education/operations/consumption-reviews/${id}/reject`, payload, operationRequestOptions(payload))
}

export function createConsumptionAdjustment(originalConsumptionId: number, payload: ConsumptionAdjustmentPayload): Promise<MineResult<{ adjustment_id: number, adjustment_consumption_id: number }>> {
  return useHttp().post(`/admin/education/operations/lesson-consumptions/${originalConsumptionId}/adjust`, payload, operationRequestOptions(payload))
}

export function pageConsumptionAdjustments(params: OperationScopedParams & { original_consumption_id?: number }): Promise<MineResult<OperationPage<ConsumptionAdjustmentRecord>>> {
  return useHttp().get('/admin/education/operations/consumption-adjustments/page', operationGetOptions(params))
}
