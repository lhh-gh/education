import type { GroupTagType } from '../../api/group/types.ts'

export function groupTagType(value?: string | null): GroupTagType {
  if (!value) {
    return ''
  }
  if (['enabled', 'approved', 'active', 'completed', 'handled', 'signed'].includes(value)) {
    return 'success'
  }
  if (['draft', 'reviewing', 'pending', 'warning', 'potential'].includes(value)) {
    return 'warning'
  }
  if (['disabled', 'rejected', 'expired', 'terminated', 'critical', 'high'].includes(value)) {
    return 'danger'
  }

  return 'info'
}

export function centsToYuan(cents?: number | null): string {
  return `\u00A5${((cents ?? 0) / 100).toFixed(2)}`
}

export function keepOrgTreeAfterCycleError<T>(rows: T[], error: { code?: number, message?: string }): { rows: T[], errorText: string } {
  if (error.code === 409) {
    return { rows, errorText: error.message ?? 'org unit parent creates cycle' }
  }

  return { rows, errorText: '' }
}

export function dataScopePreviewText(preview: { allowed_campus_ids?: number[] }): string {
  const ids = preview.allowed_campus_ids ?? []

  return ids.length > 0 ? ids.join(', ') : 'No campus scope'
}

export function canCompleteApprovalTask(task: { assignee_user_id?: number, status?: string }, currentUserId: number, hasOverride: boolean): boolean {
  return task.status === 'pending' && (task.assignee_user_id === currentUserId || hasOverride)
}

export function activeContractRiskWarning(contract: { status?: string, amount_cents?: number, risk_level?: string }, draft: { amount_cents?: number }): string {
  if (contract.status !== 'active') {
    return ''
  }
  if (contract.amount_cents !== draft.amount_cents && ['high', 'critical'].includes(contract.risk_level ?? '')) {
    return 'Active contract amount changed'
  }

  return ''
}

export function riskAuditQueryParams(input: { risk_level?: string, handled?: boolean, page?: number, pageSize?: number }): Record<string, unknown> {
  return {
    risk_level: input.risk_level,
    handled: input.handled,
    page: input.page,
    pageSize: input.pageSize,
  }
}

export function metricCards(metrics: Record<string, string | number> = {}): Array<{ title: string, value: string | number, unit?: string }> {
  return [
    { title: 'Campuses', value: metrics.campus_count ?? metrics.row_count ?? 0 },
    { title: 'Students', value: metrics.student_count ?? 0 },
    { title: 'Revenue', value: centsToYuan(Number(metrics.revenue_cents ?? 0)) },
    { title: 'Renewal Alerts', value: metrics.renewal_alert_count ?? 0 },
  ]
}
