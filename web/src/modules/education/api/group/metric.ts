import type { MineResult } from '../foundation/types.ts'
import type { GroupPage, GroupScopedParams } from './types.ts'
import { groupGetOptions } from './types.ts'

export interface GroupMetricRecord {
  id: number
  metric_date: string
  campus_count: number
  student_count: number
  revenue_cents: number
  renewal_alert_count: number
}

export interface GroupMetricParams extends GroupScopedParams {
  start_date?: string
  end_date?: string
  org_unit_id?: number
}

export function getGroupOperationMetrics(params: GroupMetricParams): Promise<MineResult<GroupPage<GroupMetricRecord>>> {
  return useHttp().get('/admin/education/group/operation-metrics', groupGetOptions(params))
}

export function getGroupOperationDashboard(params: GroupMetricParams = {}): Promise<MineResult<Record<string, number>>> {
  return useHttp().get('/admin/education/group/operation-dashboard', groupGetOptions(params))
}
