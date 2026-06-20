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

export function groupStatusLabel(value?: string | null): string {
  const labels: Record<string, string> = {
    active: '生效中',
    approved: '已通过',
    completed: '已完成',
    disabled: '停用',
    draft: '草稿',
    enabled: '启用',
    expired: '已过期',
    handled: '已处理',
    open: '未处理',
    pending: '待处理',
    potential: '潜在',
    rejected: '已拒绝',
    reviewing: '审批中',
    signed: '已签署',
    terminated: '已终止',
  }

  return value ? labels[value] ?? value : ''
}

export function groupRiskLabel(value?: string | null): string {
  const labels: Record<string, string> = {
    critical: '严重',
    high: '高风险',
    normal: '正常',
    warning: '预警',
  }

  return value ? labels[value] ?? value : ''
}

export function groupBusinessTypeLabel(value?: string | null): string {
  const labels: Record<string, string> = {
    contract: '合同',
    franchise: '加盟',
    renewal: '续签',
  }

  return value ? labels[value] ?? value : ''
}

export function groupScopeTypeLabel(value?: string | null): string {
  const labels: Record<string, string> = {
    campus_set: '指定校区',
    group_all: '集团全部',
    org_tree: '组织树',
    self: '仅本人',
  }

  return value ? labels[value] ?? value : ''
}

export function groupOrgUnitTypeLabel(value?: string | null): string {
  const labels: Record<string, string> = {
    campus_cluster: '校区集群',
    group: '集团',
    region: '区域',
  }

  return value ? labels[value] ?? value : ''
}

export function keepOrgTreeAfterCycleError<T>(rows: T[], error: { code?: number, message?: string }): { rows: T[], errorText: string } {
  if (error.code === 409) {
    return { rows, errorText: error.message ?? '组织父级不能形成循环' }
  }

  return { rows, errorText: '' }
}

export function dataScopePreviewText(preview: { allowed_campus_ids?: number[] }): string {
  const ids = preview.allowed_campus_ids ?? []

  return ids.length > 0 ? ids.join(', ') : '暂无校区范围'
}

export function canCompleteApprovalTask(task: { assignee_user_id?: number, status?: string }, currentUserId: number, hasOverride: boolean): boolean {
  return task.status === 'pending' && (task.assignee_user_id === currentUserId || hasOverride)
}

export function activeContractRiskWarning(contract: { status?: string, amount_cents?: number, risk_level?: string }, draft: { amount_cents?: number }): string {
  if (contract.status !== 'active') {
    return ''
  }
  if (contract.amount_cents !== draft.amount_cents && ['high', 'critical'].includes(contract.risk_level ?? '')) {
    return '生效合同金额发生变化'
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
    { title: '校区数', value: metrics.campus_count ?? metrics.row_count ?? 0 },
    { title: '学员数', value: metrics.student_count ?? 0 },
    { title: '营收金额', value: centsToYuan(Number(metrics.revenue_cents ?? 0)) },
    { title: '续签提醒', value: metrics.renewal_alert_count ?? 0 },
  ]
}
