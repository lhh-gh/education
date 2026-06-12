import type { AuditLogPageParams } from '../../api/foundation/auditLog.ts'
import { hasPermission } from './actionRules.ts'

export type AuditLogDateRange = [string, string] | []

export const auditLogForbiddenWriteActions = ['create', 'edit', 'status', 'delete', 'import', 'export'] as const

export function defaultAuditLogSearch(): AuditLogPageParams {
  return {
    page: 1,
    pageSize: 20,
    module: 'foundation',
    keyword: '',
  }
}

export function normalizeAuditLogSearch(search: AuditLogPageParams, dateRange: AuditLogDateRange = []): AuditLogPageParams {
  const params: AuditLogPageParams = {
    ...search,
    page: search.page || 1,
    pageSize: search.pageSize || 20,
  }

  if (dateRange.length === 2) {
    params.start_at = dateRange[0]
    params.end_at = dateRange[1]
  }
  else {
    delete params.start_at
    delete params.end_at
  }

  for (const key of Object.keys(params) as Array<keyof AuditLogPageParams>) {
    if (params[key] === '' || params[key] === undefined || params[key] === null) {
      delete params[key]
    }
  }

  return params
}

export function resetAuditLogSearch(): { search: AuditLogPageParams, dateRange: AuditLogDateRange } {
  return {
    search: defaultAuditLogSearch(),
    dateRange: [],
  }
}

export function auditLogActionsByPermission(permissions: string[]) {
  return {
    canDetail: hasPermission(permissions, 'education:foundation:audit-log:detail'),
    writeActions: [] as string[],
  }
}
