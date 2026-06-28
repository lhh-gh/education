export type PayrollTagType = '' | 'success' | 'warning' | 'danger' | 'info'

const statusLabels: Record<string, string> = {
  approved: '已通过',
  calculated: '已计算',
  cancelled: '已取消',
  closed: '已关闭',
  disabled: '停用',
  draft: '草稿',
  enabled: '启用',
  failed: '失败',
  paid: '已发放',
  pending: '待处理',
  processing: '处理中',
  rejected: '已驳回',
  submitted: '已提交',
}

const typeLabels: Record<string, string> = {
  lesson: '课次',
  performance: '绩效',
  workload: '工作量',
}

export function centsToYuan(cents?: number | null): string {
  return `\u00A5${((cents ?? 0) / 100).toFixed(2)}`
}

export function payrollTagType(value?: string | null): PayrollTagType {
  if (!value) {
    return ''
  }
  if (['enabled', 'approved', 'paid', 'closed'].includes(value)) {
    return 'success'
  }
  if (['draft', 'calculated', 'submitted', 'pending', 'processing'].includes(value)) {
    return 'warning'
  }
  if (['disabled', 'rejected', 'failed', 'cancelled'].includes(value)) {
    return 'danger'
  }

  return 'info'
}

export function payrollStatusLabel(value?: string | null): string {
  return value ? (statusLabels[value] ?? value) : ''
}

export function payrollTypeLabel(value?: string | null): string {
  return value ? (typeLabels[value] ?? value) : ''
}

export function canSelectRuleForPreview(row: { status?: string | null }): boolean {
  return row.status === 'enabled'
}

export function canRebuildBatch(row: { status?: string | null }): boolean {
  return row.status === 'draft' || row.status === 'calculated'
}

export function payrollConflictText(message?: string): string {
  return message === 'salary batch is not submitted' ? '薪酬批次未提交' : (message ?? '')
}

export function adjustmentDrawerState(response: { code: number, payable_amount_cents: number }): { open: boolean, payableAmountCents: number } {
  return {
    open: response.code !== 200,
    payableAmountCents: response.payable_amount_cents,
  }
}

export function disputeReviewPayload(status: 'approved' | 'rejected', reviewNote: string): { status: 'approved' | 'rejected', review_note: string } {
  return { status, review_note: reviewNote }
}

export function buildPerformanceParams(input: { campus_id?: number, metric_month?: string, teacher_id?: number }): Record<string, unknown> {
  return {
    campus_id: input.campus_id,
    metric_month: input.metric_month,
    teacher_id: input.teacher_id,
  }
}
