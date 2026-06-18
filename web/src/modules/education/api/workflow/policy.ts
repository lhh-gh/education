import type { MineResult } from '../foundation/types.ts'
import type { WorkflowPage, WorkflowScopedParams } from './types.ts'
import { workflowGetOptions, workflowRequestOptions } from './types.ts'

export interface SlaPolicyPayload extends WorkflowScopedParams {
  policy_code: string
  policy_name: string
  task_type: string
  due_minutes: number
}

export interface EscalationPolicyPayload extends WorkflowScopedParams {
  policy_code: string
  task_type: string
  overdue_minutes: number
  escalate_to_user_ids_json: number[]
}

export function pageSlaPolicies(params: WorkflowScopedParams): Promise<MineResult<WorkflowPage<SlaPolicyPayload>>> {
  return useHttp().get('/admin/education/workflow/sla-policies/page', workflowGetOptions(params))
}

export function saveSlaPolicy(payload: SlaPolicyPayload): Promise<MineResult<{ policy_id: number }>> {
  return useHttp().post('/admin/education/workflow/sla-policies', payload, workflowRequestOptions(payload))
}

export function pageEscalationPolicies(params: WorkflowScopedParams): Promise<MineResult<WorkflowPage<EscalationPolicyPayload>>> {
  return useHttp().get('/admin/education/workflow/escalation-policies/page', workflowGetOptions(params))
}

export function saveEscalationPolicy(payload: EscalationPolicyPayload): Promise<MineResult<{ policy_id: number }>> {
  return useHttp().post('/admin/education/workflow/escalation-policies', payload, workflowRequestOptions(payload))
}
