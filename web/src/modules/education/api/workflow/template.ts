import type { MineResult } from '../foundation/types.ts'
import type { WorkflowPage, WorkflowScopedParams } from './types.ts'
import { workflowGetOptions, workflowRequestOptions } from './types.ts'

export interface WorkflowTemplatePayload extends WorkflowScopedParams {
  template_code: string
  template_name: string
  task_type: string
  template_json: Record<string, unknown>
}

export function pageWorkflowTemplates(params: WorkflowScopedParams): Promise<MineResult<WorkflowPage<WorkflowTemplatePayload>>> {
  return useHttp().get('/admin/education/workflow/templates/page', workflowGetOptions(params))
}

export function saveWorkflowTemplate(payload: WorkflowTemplatePayload): Promise<MineResult<{ template_id: number }>> {
  return useHttp().post('/admin/education/workflow/templates', payload, workflowRequestOptions(payload))
}
