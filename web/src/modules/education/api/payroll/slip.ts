import type { MineResult } from '../foundation/types.ts'
import type { PayrollPage, PayrollScopedParams } from './types.ts'
import { payrollGetOptions, payrollRequestOptions } from './types.ts'

export interface SalarySlipRecord {
  id: number
  batch_id: number
  teacher_id: number
  teacher_name?: string
  salary_month: string
  status: string
  gross_amount_cents: number
  deduction_amount_cents?: number
  adjustment_amount_cents?: number
  payable_amount_cents: number
  paid_amount_cents?: number
}

export interface SalaryReviewRecord {
  id: number
  batch_id: number
  reviewer_id?: number | null
  status: string
  review_note?: string | null
  reviewed_at?: string | null
}

export interface SalaryAdjustmentPayload extends PayrollScopedParams {
  amount_cents: number
  reason: string
}

export function pageSalarySlips(params: PayrollScopedParams): Promise<MineResult<PayrollPage<SalarySlipRecord>>> {
  return useHttp().get('/admin/education/payroll/salary-slips/page', payrollGetOptions(params))
}

export function createSalaryAdjustment(id: number, payload: SalaryAdjustmentPayload): Promise<MineResult<{ slip_id: number, payable_amount_cents: number }>> {
  return useHttp().post(`/admin/education/payroll/salary-slips/${id}/adjustments`, payload, payrollRequestOptions(payload))
}

export function pageSalaryReviews(params: PayrollScopedParams): Promise<MineResult<PayrollPage<SalaryReviewRecord>>> {
  return useHttp().get('/admin/education/payroll/salary-reviews/page', payrollGetOptions(params))
}
