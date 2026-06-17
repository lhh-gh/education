import type { OperationScopedParams, PageResult } from './shared'
import { requestOperation, selectedStudentId } from './shared'

export { MobileApiError } from './shared'

export interface GuardianOperationParams extends OperationScopedParams {
  status?: string
}

export interface GuardianChangedLesson {
  id: number
  lesson_id: number
  change_type: string
  status: string
  reason?: string
  created_at?: string | null
}

export interface GuardianMakeupEntitlement {
  id: number
  student_id: number
  course_id: number
  source_lesson_id: number
  status: 'available' | 'arranged' | 'used' | 'expired' | 'cancelled'
  expires_at?: string | null
  course_name?: string | null
}

export interface GuardianMakeupRecord {
  id: number
  makeup_entitlement_id: number
  makeup_lesson_id: number
  student_id: number
  status: 'arranged' | 'completed' | 'cancelled'
  arranged_at?: string | null
}

export interface GuardianRenewalAlert {
  id: number
  student_id: number
  course_id: number
  alert_type: string
  alert_level: 'normal' | 'warning' | 'urgent'
  status: 'open' | 'ignored' | 'closed'
  trigger_value?: string | null
  threshold_value?: string | null
  due_date?: string | null
}

export function getChangedLessons(params?: GuardianOperationParams): Promise<PageResult<GuardianChangedLesson>> {
  return requestGuardianList('/mobile/education/operations/guardian/changed-lessons', params)
}

export function getMakeupEntitlements(params?: GuardianOperationParams): Promise<PageResult<GuardianMakeupEntitlement>> {
  return requestGuardianList('/mobile/education/operations/guardian/makeup-entitlements', params)
}

export function getMakeupRecords(params?: GuardianOperationParams): Promise<PageResult<GuardianMakeupRecord>> {
  return requestGuardianList('/mobile/education/operations/guardian/makeup-records', params)
}

export function getRenewalAlerts(params?: GuardianOperationParams): Promise<PageResult<GuardianRenewalAlert>> {
  return requestGuardianList('/mobile/education/operations/guardian/renewal-alerts', params)
}

function requestGuardianList<T>(url: string, params?: GuardianOperationParams): Promise<PageResult<T>> {
  return requestOperation(url, 'GET', { ...params, student_id: selectedStudentId(params) })
}
