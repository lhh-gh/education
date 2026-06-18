export type FinanceTagType = '' | 'success' | 'warning' | 'danger' | 'info'

export interface FinancePermissions {
  offlinePayment: boolean
  refundApprove: boolean
  receiptIssue: boolean
}

export function centsToYuan(cents?: number | null): string {
  return `¥${((cents ?? 0) / 100).toFixed(2)}`
}

export function financeTagType(value?: string | null): FinanceTagType {
  if (!value) {
    return ''
  }
  if (['paid', 'approved', 'issued', 'matched', 'enabled'].includes(value)) {
    return 'success'
  }
  if (['pending', 'paying', 'processing', 'imported', 'partially_matched'].includes(value)) {
    return 'warning'
  }
  if (['failed', 'rejected', 'cancelled', 'voided', 'exception'].includes(value)) {
    return 'danger'
  }
  if (['refunded', 'partial_refunded', 'closed', 'unmatched'].includes(value)) {
    return 'info'
  }

  return ''
}

export function financePermissions(has: (code: string) => boolean): FinancePermissions {
  return {
    offlinePayment: has('education:finance:payment:offline'),
    refundApprove: has('education:finance:refund:approve'),
    receiptIssue: has('education:finance:receipt:issue'),
  }
}

export function canShowOfflineCollection(has: (code: string) => boolean): boolean {
  return financePermissions(has).offlinePayment
}

export function validateRefundAmount(amountCents: number, refundableCents: number): string {
  if (amountCents <= 0) {
    return 'Refund amount must be positive'
  }
  if (amountCents > refundableCents) {
    return 'Refund amount exceeds refundable amount'
  }

  return ''
}

export function reconciliationRowState(row: { match_status?: string | null }): 'normal' | 'exception' {
  return row.match_status === 'unmatched' ? 'exception' : 'normal'
}

export function duplicateCallbackText(message?: string): string {
  return message === 'payment already processed' ? 'payment already processed' : (message ?? '')
}

export function buildFinanceDashboardParams(input: { tenant_id?: number, campus_id?: number, start_at?: string, end_at?: string }): Record<string, unknown> {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    start_at: input.start_at,
    end_at: input.end_at,
  }
}
