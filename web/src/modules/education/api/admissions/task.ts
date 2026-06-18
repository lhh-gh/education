import type { MineResult } from '../foundation/types.ts'
import type { AdmissionPage, AdmissionScopedParams } from './types.ts'
import { admissionGetOptions, admissionRequestOptions } from './types.ts'

export interface AdmissionTaskRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  lead_id?: number | null
  task_type: string
  title: string
  assignee_user_id: number
  status: string
  due_at?: string | null
}

export function pageAdmissionTasks(params: AdmissionScopedParams & { status?: string, assignee_user_id?: number }): Promise<MineResult<AdmissionPage<AdmissionTaskRecord>>> {
  return useHttp().get('/admin/education/admissions/tasks/page', admissionGetOptions(params))
}

export function completeAdmissionTask(id: number, payload: { tenant_id?: number, campus_id?: number }): Promise<MineResult<AdmissionTaskRecord>> {
  return useHttp().post(`/admin/education/admissions/tasks/${id}/complete`, {}, admissionRequestOptions(payload))
}

export function cancelAdmissionTask(id: number, payload: { tenant_id?: number, campus_id?: number }): Promise<MineResult<AdmissionTaskRecord>> {
  return useHttp().post(`/admin/education/admissions/tasks/${id}/cancel`, {}, admissionRequestOptions(payload))
}
