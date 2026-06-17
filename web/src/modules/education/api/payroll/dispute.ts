import type { MineResult } from '../foundation/types.ts'
import type { PayrollPage, PayrollScopedParams } from './types.ts'
import { payrollGetOptions, payrollRequestOptions } from './types.ts'

export interface WorkloadDisputeRecord {
  id: number
  teacher_id: number
  teacher_name?: string
  workload_id: number
  salary_month: string
  status: string
  reason: string
  review_note?: string | null
}

export interface WorkloadDisputeReviewPayload extends PayrollScopedParams {
  status: 'approved' | 'rejected'
  review_note: string
}

export function pageWorkloadDisputes(params: PayrollScopedParams): Promise<MineResult<PayrollPage<WorkloadDisputeRecord>>> {
  return useHttp().get('/admin/education/payroll/workload-disputes/page', payrollGetOptions(params))
}

export function reviewWorkloadDispute(id: number, payload: WorkloadDisputeReviewPayload): Promise<MineResult<WorkloadDisputeRecord>> {
  return useHttp().post(`/admin/education/payroll/workload-disputes/${id}/review`, payload, payrollRequestOptions(payload))
}
