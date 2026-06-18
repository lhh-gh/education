import type { MineResult } from '../foundation/types.ts'
import type { PayrollPage, PayrollScopedParams } from './types.ts'
import { payrollGetOptions, payrollRequestOptions } from './types.ts'

export interface SalaryBatchRecord {
  id: number
  batch_no: string
  salary_month: string
  campus_id?: number | null
  status: string
  teacher_count?: number
  gross_amount_cents?: number
  payable_amount_cents?: number
  submitted_at?: string | null
  approved_at?: string | null
}

export interface SalaryBatchCalculatePayload extends PayrollScopedParams {
  salary_month: string
  teacher_ids?: number[]
}

export function pageSalaryBatches(params: PayrollScopedParams): Promise<MineResult<PayrollPage<SalaryBatchRecord>>> {
  return useHttp().get('/admin/education/payroll/salary-batches/page', payrollGetOptions(params))
}

export function calculateSalaryBatch(payload: SalaryBatchCalculatePayload): Promise<MineResult<SalaryBatchRecord>> {
  return useHttp().post('/admin/education/payroll/salary-batches/calculate', payload, payrollRequestOptions(payload))
}

export function rebuildSalaryBatch(id: number, payload: PayrollScopedParams = {}): Promise<MineResult<SalaryBatchRecord>> {
  return useHttp().post(`/admin/education/payroll/salary-batches/${id}/rebuild`, payload, payrollRequestOptions(payload))
}

export function submitSalaryBatchReview(id: number, payload: PayrollScopedParams = {}): Promise<MineResult<SalaryBatchRecord>> {
  return useHttp().post(`/admin/education/payroll/salary-batches/${id}/submit-review`, payload, payrollRequestOptions(payload))
}

export function approveSalaryBatch(id: number, payload: PayrollScopedParams & { review_note?: string } = {}): Promise<MineResult<SalaryBatchRecord>> {
  return useHttp().post(`/admin/education/payroll/salary-batches/${id}/approve`, payload, payrollRequestOptions(payload))
}

export function rejectSalaryBatch(id: number, payload: PayrollScopedParams & { review_note?: string } = {}): Promise<MineResult<SalaryBatchRecord>> {
  return useHttp().post(`/admin/education/payroll/salary-batches/${id}/reject`, payload, payrollRequestOptions(payload))
}
