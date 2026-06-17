import type { RenewalAlertRecord } from '../../api/operations/renewal.ts'

export type OperationBadgeType = '' | 'success' | 'warning' | 'danger' | 'info'

export interface OperationPermissions {
  approveConsumption: boolean
  adjustConsumption: boolean
  batchLessonChange: boolean
  arrangeMakeup: boolean
  renewalFollow: boolean
}

export function operationTagType(value?: string | null): OperationBadgeType {
  if (!value) {
    return ''
  }
  if (['approved', 'applied', 'arranged', 'completed', 'used', 'closed'].includes(value)) {
    return 'success'
  }
  if (['pending', 'available', 'following', 'warning'].includes(value)) {
    return 'warning'
  }
  if (['rejected', 'cancelled', 'expired', 'urgent'].includes(value)) {
    return 'danger'
  }
  if (['ignored', 'normal', 'substitute'].includes(value)) {
    return 'info'
  }

  return ''
}

export function operationPermissions(has: (code: string) => boolean): OperationPermissions {
  return {
    approveConsumption: has('education:operations:consumption-review:approve'),
    adjustConsumption: has('education:operations:consumption-adjustment:create'),
    batchLessonChange: has('education:operations:lesson-change:batch'),
    arrangeMakeup: has('education:operations:makeup:arrange'),
    renewalFollow: has('education:operations:renewal-task:follow'),
  }
}

export function sortRenewalAlerts(alerts: RenewalAlertRecord[]): RenewalAlertRecord[] {
  const weight: Record<string, number> = { urgent: 0, warning: 1, normal: 2 }

  return [...alerts].sort((left, right) => {
    const due = String(left.due_date ?? '').localeCompare(String(right.due_date ?? ''))
    if (due !== 0) {
      return due
    }

    return (weight[left.alert_level] ?? 9) - (weight[right.alert_level] ?? 9)
  })
}

export function operationDashboardMetricItems(metrics: Record<string, string | number> = {}): Array<{ title: string, value: string | number, unit?: string }> {
  return [
    { title: 'Lesson Changes', value: metrics.lesson_change_count ?? 0 },
    { title: 'Make-up Open', value: metrics.makeup_available_count ?? 0 },
    { title: 'Pending Reviews', value: metrics.pending_review_count ?? 0 },
    { title: 'Urgent Renewals', value: metrics.urgent_renewal_count ?? 0 },
    { title: 'Teacher Credits', value: metrics.teacher_credit_count ?? '0.00', unit: 'credits' },
  ]
}

export function conflictErrorText(error: any): string {
  if (error?.response?.status === 409 || error?.code === 409) {
    return error?.message ?? 'Lesson change conflict'
  }

  return error?.message ?? 'Operation failed'
}

export function buildDashboardChartParams(input: { tenant_id?: number, campus_id?: number, start_at?: string, end_at?: string }): Record<string, unknown> {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    start_at: input.start_at,
    end_at: input.end_at,
  }
}
