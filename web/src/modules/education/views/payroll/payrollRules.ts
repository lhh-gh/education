export type PayrollTagType = '' | 'success' | 'warning' | 'danger' | 'info'

export function centsToYuan(cents?: number | null): string {
  return `\u00a5${((cents ?? 0) / 100).toFixed(2)}`
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

export function canSelectRuleForPreview(row: { status?: string | null }): boolean {
  return row.status === 'enabled'
}

export function canRebuildBatch(row: { status?: string | null }): boolean {
  return row.status === 'draft' || row.status === 'calculated'
}

export function payrollConflictText(message?: string): string {
  return message === 'salary batch is not submitted' ? 'salary batch is not submitted' : (message ?? '')
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
