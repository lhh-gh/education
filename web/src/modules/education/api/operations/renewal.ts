import type { MineResult } from '../foundation/types.ts'
import type { OperationPage, OperationScopedParams } from './types.ts'
import { operationGetOptions, operationRequestOptions } from './types.ts'

export type RenewalAlertStatus = 'open' | 'ignored' | 'closed'
export type RenewalAlertLevel = 'normal' | 'warning' | 'urgent'
export type RenewalTaskStatus = 'pending' | 'following' | 'closed'

export interface RenewalAlertRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  student_id: number
  course_id: number
  student_course_account_id: number
  alert_type: string
  alert_level: RenewalAlertLevel
  status: RenewalAlertStatus
  trigger_value?: string | null
  threshold_value?: string | null
  due_date?: string | null
  assignee_id?: number | null
}

export interface RenewalTaskRecord {
  id: number
  renewal_alert_id: number
  assignee_id: number
  status: RenewalTaskStatus
  next_follow_at?: string | null
}

export interface StudentFollowRecord {
  id: number
  tenant_id: number
  student_id: number
  follow_type: 'phone' | 'wechat' | 'offline' | 'system'
  content: string
  next_follow_at?: string | null
  created_at?: string | null
}

export interface RenewalAlertPageParams extends OperationScopedParams {
  status?: RenewalAlertStatus
  alert_type?: string
  alert_level?: RenewalAlertLevel
  assignee_id?: number
}

export interface RenewalTaskAssignPayload {
  tenant_id?: number
  campus_id?: number
  renewal_alert_id: number
  assignee_id: number
  next_follow_at?: string
}

export interface RenewalFollowPayload {
  tenant_id?: number
  campus_id?: number
  student_id: number
  follow_type: 'phone' | 'wechat' | 'offline' | 'system'
  content: string
  next_follow_at?: string
}

export function pageRenewalAlerts(params: RenewalAlertPageParams): Promise<MineResult<OperationPage<RenewalAlertRecord>>> {
  return useHttp().get('/admin/education/operations/renewal-alerts/page', operationGetOptions(params))
}

export function ignoreRenewalAlert(id: number, payload: { tenant_id?: number, campus_id?: number } = {}): Promise<MineResult<RenewalAlertRecord>> {
  return useHttp().post(`/admin/education/operations/renewal-alerts/${id}/ignore`, payload, operationRequestOptions(payload))
}

export function closeRenewalAlert(id: number, payload: { tenant_id?: number, campus_id?: number } = {}): Promise<MineResult<RenewalAlertRecord>> {
  return useHttp().post(`/admin/education/operations/renewal-alerts/${id}/close`, payload, operationRequestOptions(payload))
}

export function assignRenewalTask(payload: RenewalTaskAssignPayload): Promise<MineResult<RenewalTaskRecord>> {
  return useHttp().post('/admin/education/operations/renewal-tasks/assign', payload, operationRequestOptions(payload))
}

export function followRenewalTask(id: number, payload: RenewalFollowPayload): Promise<MineResult<{ follow_record_id: number, task_status: RenewalTaskStatus }>> {
  return useHttp().post(`/admin/education/operations/renewal-tasks/${id}/follow`, payload, operationRequestOptions(payload))
}

export function pageStudentFollowRecords(params: OperationScopedParams & { student_id?: number }): Promise<MineResult<OperationPage<StudentFollowRecord>>> {
  return useHttp().get('/admin/education/operations/student-follow-records/page', operationGetOptions(params))
}
