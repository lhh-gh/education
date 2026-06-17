import type { MineResult } from '../foundation/types.ts'
import type { AdmissionPage, AdmissionScopedParams } from './types.ts'
import { admissionGetOptions, admissionRequestOptions } from './types.ts'

export interface LeadSourceRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  code: string
  name: string
  channel_type: string
  default_consultant_id?: number | null
  status: 'enabled' | 'disabled'
  sort_order: number
  remark?: string | null
}

export interface LeadSourcePayload {
  tenant_id?: number
  campus_id?: number
  code: string
  name: string
  channel_type: string
  default_consultant_id?: number
  status?: 'enabled' | 'disabled'
  sort_order?: number
  remark?: string
}

export function pageLeadSources(params: AdmissionScopedParams): Promise<MineResult<AdmissionPage<LeadSourceRecord>>> {
  return useHttp().get('/admin/education/admissions/lead-sources/page', admissionGetOptions(params))
}

export function createLeadSource(payload: LeadSourcePayload): Promise<MineResult<LeadSourceRecord>> {
  return useHttp().post('/admin/education/admissions/lead-sources', payload, admissionRequestOptions(payload))
}

export function updateLeadSource(payload: LeadSourcePayload & { id: number }): Promise<MineResult<LeadSourceRecord>> {
  return useHttp().post('/admin/education/admissions/lead-sources', payload, admissionRequestOptions(payload))
}

export function updateLeadSourceStatus(payload: { id: number, tenant_id?: number, campus_id?: number, status: 'enabled' | 'disabled' }): Promise<MineResult<LeadSourceRecord>> {
  return useHttp().post('/admin/education/admissions/lead-sources', payload, admissionRequestOptions(payload))
}
