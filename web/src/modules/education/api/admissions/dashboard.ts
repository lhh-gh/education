import type { MineResult } from '../foundation/types.ts'
import type { AdmissionScopedParams } from './types.ts'
import { admissionGetOptions } from './types.ts'

export interface AdmissionOverview {
  new_leads_count: number
  follow_count: number
  trial_count: number
  trial_attended_count: number
  converted_count: number
  trial_attendance_rate: number
  conversion_rate: number
}

export function getAdmissionOverview(params: AdmissionScopedParams & { start_date?: string, end_date?: string }): Promise<MineResult<AdmissionOverview>> {
  return useHttp().get('/admin/education/admissions/dashboard/overview', admissionGetOptions(params))
}

export const getAdmissionFunnel = getAdmissionOverview
export const getSourceSummary = getAdmissionOverview
export const getConsultantSummary = getAdmissionOverview
