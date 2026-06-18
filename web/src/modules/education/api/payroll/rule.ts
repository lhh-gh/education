import type { MineResult } from '../foundation/types.ts'
import type { PayrollPage, PayrollScopedParams } from './types.ts'
import { payrollGetOptions, payrollRequestOptions } from './types.ts'

export interface SalaryRuleRecord {
  id: number
  rule_name: string
  rule_type: string
  teacher_level?: string | null
  course_type?: string | null
  base_amount_cents: number
  unit_amount_cents: number
  status: string
  effective_start?: string | null
  effective_end?: string | null
}

export interface SalaryRulePayload extends PayrollScopedParams {
  rule_name: string
  rule_type: string
  teacher_level?: string
  course_type?: string
  base_amount_cents: number
  unit_amount_cents: number
  status: string
  effective_start?: string
  effective_end?: string
}

export function pageSalaryRules(params: PayrollScopedParams): Promise<MineResult<PayrollPage<SalaryRuleRecord>>> {
  return useHttp().get('/admin/education/payroll/salary-rules/page', payrollGetOptions(params))
}

export function saveSalaryRule(payload: SalaryRulePayload): Promise<MineResult<SalaryRuleRecord>> {
  return useHttp().post('/admin/education/payroll/salary-rules', payload, payrollRequestOptions(payload))
}
