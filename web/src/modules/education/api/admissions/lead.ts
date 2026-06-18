import type { MineResult } from '../foundation/types.ts'
import type { AdmissionPage, AdmissionScopedParams } from './types.ts'
import { admissionGetOptions, admissionRequestOptions } from './types.ts'

export type LeadStage = 'new' | 'assigned' | 'followed' | 'trial_scheduled' | 'trial_done' | 'converted' | 'lost'
export type LeadStatus = 'active' | 'converted' | 'lost' | 'invalid'

export interface LeadRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  lead_no: string
  source_id?: number | null
  contact_name: string
  contact_mobile: string
  contact_wechat?: string | null
  stage: LeadStage
  status: LeadStatus
  owner_user_id?: number | null
  intention_course_id?: number | null
  intention_level: 'low' | 'medium' | 'high'
  next_follow_at?: string | null
  last_follow_at?: string | null
  remark?: string | null
}

export interface LeadPageParams extends AdmissionScopedParams {
  source_id?: number
  stage?: LeadStage
  status?: LeadStatus
  owner_user_id?: number
}

export interface LeadCreatePayload {
  tenant_id?: number
  campus_id?: number
  contact_name: string
  contact_mobile: string
  source_id?: number
  lead_students?: Array<{ name: string, grade?: string }>
}

export function pageLeads(params: LeadPageParams): Promise<MineResult<AdmissionPage<LeadRecord>>> {
  return useHttp().get('/admin/education/admissions/leads/page', admissionGetOptions(params))
}

export function createLead(payload: LeadCreatePayload): Promise<MineResult<LeadRecord>> {
  return useHttp().post('/admin/education/admissions/leads', payload, admissionRequestOptions(payload))
}

export function updateLead(payload: LeadCreatePayload & { id: number }): Promise<MineResult<LeadRecord>> {
  return useHttp().post('/admin/education/admissions/leads', payload, admissionRequestOptions(payload))
}

export function assignLead(id: number, payload: { tenant_id?: number, campus_id?: number, to_user_id: number, reason?: string }): Promise<MineResult<Record<string, unknown>>> {
  return useHttp().post(`/admin/education/admissions/leads/${id}/assign`, payload, admissionRequestOptions(payload))
}

export function addFollowRecord(id: number, payload: { tenant_id?: number, campus_id?: number, follow_type: string, content: string, next_follow_at?: string }): Promise<MineResult<Record<string, unknown>>> {
  return useHttp().post(`/admin/education/admissions/leads/${id}/follow-records`, payload, admissionRequestOptions(payload))
}

export function getLeadDetail(id: number, params: AdmissionScopedParams): Promise<MineResult<LeadRecord & { guardians?: unknown[], students?: unknown[] }>> {
  return useHttp().get(`/admin/education/admissions/leads/${id}`, admissionGetOptions(params))
}

export function convertLead(id: number, payload: { tenant_id?: number, campus_id?: number, student_name: string, guardian_name: string, lesson_package_id: number, paid_amount?: string }): Promise<MineResult<Record<string, number>>> {
  return useHttp().post(`/admin/education/admissions/leads/${id}/convert`, payload, admissionRequestOptions(payload))
}
