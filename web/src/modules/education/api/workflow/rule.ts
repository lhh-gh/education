import type { MineResult } from '../foundation/types.ts'
import type { WorkflowPage, WorkflowScopedParams } from './types.ts'
import { workflowGetOptions, workflowRequestOptions } from './types.ts'

export interface WorkflowRuleActionPayload {
  action_type: string
  action_config_json: Record<string, unknown>
}

export interface WorkflowRuleConditionPayload {
  condition_field: string
  operator: string
  condition_value_json: unknown
}

export interface WorkflowRuleRecord {
  id: number
  rule_code: string
  rule_name: string
  event_type: string
  status: 'enabled' | 'disabled'
  priority: number
}

export interface WorkflowRulePayload extends WorkflowScopedParams {
  rule_code: string
  rule_name: string
  event_type: string
  conditions?: WorkflowRuleConditionPayload[]
  actions: WorkflowRuleActionPayload[]
}

export function pageWorkflowRules(params: WorkflowScopedParams): Promise<MineResult<WorkflowPage<WorkflowRuleRecord>>> {
  return useHttp().get('/admin/education/workflow/rules/page', workflowGetOptions(params))
}

export function saveWorkflowRule(payload: WorkflowRulePayload): Promise<MineResult<{ rule_id: number, status: string }>> {
  return useHttp().post('/admin/education/workflow/rules', payload, workflowRequestOptions(payload))
}

export function enableWorkflowRule(id: number, payload: WorkflowScopedParams & { enabled: boolean }): Promise<MineResult<{ rule_id: number, status: string }>> {
  return useHttp().post(`/admin/education/workflow/rules/${id}/enable`, payload, workflowRequestOptions(payload))
}

export function disableWorkflowRule(id: number, payload: WorkflowScopedParams = {}): Promise<MineResult<{ rule_id: number, status: string }>> {
  return enableWorkflowRule(id, { ...payload, enabled: false })
}
