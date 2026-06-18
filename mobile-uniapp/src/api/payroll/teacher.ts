import type { OperationScopedParams, PageResult } from '../operations/shared'
import { requestOperation } from '../operations/shared'

export { MobileApiError } from '../operations/shared'

export type TeacherSalarySlipStatus = 'draft' | 'submitted' | 'approved' | 'paid' | 'rejected'
export type TeacherWorkloadDisputeStatus = 'pending' | 'approved' | 'rejected'

export interface TeacherPayrollParams extends OperationScopedParams {
  salary_month?: string
  status?: string
}

export interface TeacherSalarySlip {
  id: number
  batch_id: number
  salary_month: string
  status: TeacherSalarySlipStatus | string
  gross_amount_cents: number
  deduction_amount_cents?: number
  adjustment_amount_cents?: number
  payable_amount_cents: number
  paid_amount_cents?: number
}

export interface TeacherWorkloadDispute {
  id: number
  source_workload_id: number
  salary_slip_id?: number | null
  salary_month?: string | null
  dispute_type: string
  content: string
  status: TeacherWorkloadDisputeStatus | string
  review_note?: string | null
}

export interface TeacherWorkloadDisputePayload extends OperationScopedParams {
  source_workload_id: number
  salary_slip_id?: number
  dispute_type: string
  content: string
}

export function getTeacherSalarySlips(params?: TeacherPayrollParams): Promise<PageResult<TeacherSalarySlip>> {
  return requestOperation('/mobile/education/payroll/teacher/slips', 'GET', params)
}

export function getTeacherWorkloadDisputes(params?: TeacherPayrollParams): Promise<PageResult<TeacherWorkloadDispute>> {
  return requestOperation('/mobile/education/payroll/teacher/disputes', 'GET', params)
}

export function submitTeacherWorkloadDispute(payload: TeacherWorkloadDisputePayload): Promise<TeacherWorkloadDispute> {
  return requestOperation('/mobile/education/payroll/teacher/disputes', 'POST', payload)
}
