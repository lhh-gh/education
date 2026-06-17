import type { MineResult } from '../foundation/types.ts'
import type { PayrollPage, PayrollScopedParams } from './types.ts'
import { payrollGetOptions, payrollRequestOptions } from './types.ts'

export interface SalaryPaymentRecord {
  id: number
  slip_id: number
  payment_no: string
  teacher_id: number
  amount_cents: number
  status: string
  paid_at?: string | null
}

export interface SalaryPaymentPayload extends PayrollScopedParams {
  slip_id: number
  amount_cents: number
  payment_no?: string
  paid_at?: string
  remark?: string
}

export function pageSalaryPayments(params: PayrollScopedParams): Promise<MineResult<PayrollPage<SalaryPaymentRecord>>> {
  return useHttp().get('/admin/education/payroll/salary-payments/page', payrollGetOptions(params))
}

export function createSalaryPayment(payload: SalaryPaymentPayload): Promise<MineResult<SalaryPaymentRecord>> {
  return useHttp().post('/admin/education/payroll/salary-payments', payload, payrollRequestOptions(payload))
}
