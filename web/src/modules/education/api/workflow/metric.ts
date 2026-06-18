import type { MineResult } from '../foundation/types.ts'
import type { WorkflowScopedParams } from './types.ts'
import { workflowGetOptions } from './types.ts'

export interface WorkflowMetricSummary {
  created_count: number
  completed_count: number
  overdue_count: number
  alert_count: number
}

export function getWorkflowMetrics(params: WorkflowScopedParams): Promise<MineResult<WorkflowMetricSummary>> {
  return useHttp().get('/admin/education/workflow/metrics', workflowGetOptions(params))
}

export function getWorkflowDashboard(params: WorkflowScopedParams): Promise<MineResult<WorkflowMetricSummary>> {
  return useHttp().get('/admin/education/workflow/dashboard', workflowGetOptions(params))
}
