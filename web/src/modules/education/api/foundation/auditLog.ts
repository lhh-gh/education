import type { PageList, ResponseStruct } from '#/global'

export type AuditActorType = 'admin' | 'teacher' | 'guardian' | 'system'

export interface AuditLogListItem {
  id: number
  tenant_id: number | null
  campus_id: number | null
  actor_user_id: number | null
  actor_type: AuditActorType
  actor_role_code: string | null
  module: string
  resource: string
  action: string
  business_type: string
  business_id: string | null
  request_id: string | null
  ip_address: string | null
  method: string | null
  path: string | null
  summary: string | null
  created_at: string
}

export interface AuditLogDetail extends AuditLogListItem {
  user_agent: string | null
  before_snapshot: Record<string, unknown> | null
  after_snapshot: Record<string, unknown> | null
  diff: Record<string, { before: unknown, after: unknown }> | null
  metadata: Record<string, unknown> | null
}

export interface AuditLogPageParams {
  page: number
  pageSize: number
  tenant_id?: number
  campus_id?: number
  module?: string
  resource?: string
  action?: string
  business_type?: string
  business_id?: string
  actor_user_id?: number
  actor_type?: AuditActorType
  keyword?: string
  start_at?: string
  end_at?: string
}

function tenantConfig(tenantId?: number): { headers: Record<string, string> } | undefined {
  return tenantId && tenantId > 0
    ? { headers: { 'X-Tenant-Id': String(tenantId) } }
    : undefined
}

export function pageAuditLogs(params: AuditLogPageParams): Promise<ResponseStruct<PageList<AuditLogListItem>>> {
  return useHttp().get('/admin/education/foundation/audit-logs/page', {
    params,
    ...tenantConfig(params.tenant_id),
  })
}

export function getAuditLogDetail(id: number): Promise<ResponseStruct<AuditLogDetail>> {
  return useHttp().get(`/admin/education/foundation/audit-logs/${id}`)
}
