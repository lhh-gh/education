import type { MineResult } from '../foundation/types.ts'
import type { GroupPage, GroupScopedParams } from './types.ts'
import { groupGetOptions, groupRequestOptions } from './types.ts'

export interface RiskAuditRecord {
  id: number
  event_type: string
  risk_level: string
  business_type: string
  summary: string
  handled: boolean
}

export interface RiskAuditParams extends GroupScopedParams {
  risk_level?: string
  handled?: boolean
}

export function pageRiskAuditEvents(params: RiskAuditParams): Promise<MineResult<GroupPage<RiskAuditRecord>>> {
  return useHttp().get('/admin/education/group/risk-audit-events/page', groupGetOptions(params))
}

export function markRiskAuditHandled(id: number, params: GroupScopedParams = {}): Promise<MineResult<RiskAuditRecord>> {
  return useHttp().post(`/admin/education/group/risk-audit-events/${id}/handled`, {}, groupRequestOptions(params))
}
