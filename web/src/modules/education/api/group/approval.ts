import type { MineResult } from '../foundation/types.ts'
import type { GroupPage, GroupScopedParams } from './types.ts'
import { groupGetOptions, groupRequestOptions } from './types.ts'

export interface ApprovalTemplateRecord {
  id: number
  template_code: string
  template_name: string
  business_type: string
  status: string
}

export interface ApprovalTaskRecord {
  id: number
  approval_instance_id: number
  assignee_user_id: number
  status: string
  result?: string | null
}

export interface ApprovalTemplatePayload extends GroupScopedParams {
  template_code: string
  template_name: string
  business_type: string
  nodes: Array<{ node_code?: string, node_name?: string, assignee_user_id?: number, sort_order?: number }>
}

export interface ApprovalInstancePayload extends GroupScopedParams {
  template_id: number
  business_type: string
  business_id: number
  payload_json?: Record<string, unknown>
}

export interface ApprovalTaskCompletePayload extends GroupScopedParams {
  result: 'approved' | 'rejected'
  comment?: string
  override_permission?: boolean
}

export function pageApprovalTemplates(params: GroupScopedParams): Promise<MineResult<GroupPage<ApprovalTemplateRecord>>> {
  return useHttp().get('/admin/education/group/approval-templates/page', groupGetOptions(params))
}

export function saveApprovalTemplate(payload: ApprovalTemplatePayload): Promise<MineResult<{ template_id: number, status: string }>> {
  return useHttp().post('/admin/education/group/approval-templates', payload, groupRequestOptions(payload))
}

export function createApprovalInstance(payload: ApprovalInstancePayload): Promise<MineResult<{ approval_instance_id: number, task_id: number, status: string }>> {
  return useHttp().post('/admin/education/group/approval-instances', payload, groupRequestOptions(payload))
}

export function pageApprovalTasks(params: GroupScopedParams): Promise<MineResult<GroupPage<ApprovalTaskRecord>>> {
  return useHttp().get('/admin/education/group/approval-tasks/page', groupGetOptions(params))
}

export function completeApprovalTask(id: number, payload: ApprovalTaskCompletePayload): Promise<MineResult<Record<string, unknown>>> {
  return useHttp().post(`/admin/education/group/approval-tasks/${id}/complete`, payload, groupRequestOptions(payload))
}
