import type { RenewalAlertRecord } from '../../api/operations/renewal.ts'

export type OperationBadgeType = '' | 'success' | 'warning' | 'danger' | 'info'

export interface OperationPermissions {
  approveConsumption: boolean
  adjustConsumption: boolean
  batchLessonChange: boolean
  arrangeMakeup: boolean
  renewalFollow: boolean
}

const statusLabels: Record<string, string> = {
  approved: '已通过',
  applied: '已生效',
  arranged: '已安排',
  available: '可补课',
  cancelled: '已取消',
  closed: '已关闭',
  completed: '已完成',
  expired: '已过期',
  following: '跟进中',
  ignored: '已忽略',
  normal: '普通',
  open: '待跟进',
  pending: '待处理',
  rejected: '已驳回',
  urgent: '紧急',
  used: '已使用',
  warning: '预警',
}

const changeTypeLabels: Record<string, string> = {
  cancel: '取消课次',
  replace_classroom: '更换教室',
  replace_teacher: '更换教师',
  reschedule: '改期',
  substitute: '代课',
  substitute_teacher: '安排代课',
  suspend: '停课',
}

const workloadTypeLabels: Record<string, string> = {
  main: '主讲',
  substitute: '代课',
}

const alertTypeLabels: Record<string, string> = {
  balance_low: '课时不足',
  course_expiring: '课程到期',
  no_follow: '未跟进',
  renewal_due: '续费到期',
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

export function operationStatusLabel(value?: string | null): string {
  return value ? (statusLabels[value] ?? operationTypeLabel(value)) : ''
}

export function operationTypeLabel(value?: string | null): string {
  if (!value) {
    return ''
  }

  return changeTypeLabels[value] ?? workloadTypeLabels[value] ?? alertTypeLabels[value] ?? value
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
    { title: '调课申请', value: metrics.lesson_change_count ?? 0 },
    { title: '待补课', value: metrics.makeup_available_count ?? 0 },
    { title: '待审核', value: metrics.pending_review_count ?? 0 },
    { title: '紧急续费', value: metrics.urgent_renewal_count ?? 0 },
    { title: '教师课时', value: metrics.teacher_credit_count ?? '0.00', unit: '课时' },
  ]
}

export function conflictErrorText(error: any): string {
  if (error?.response?.status === 409 || error?.code === 409) {
    return error?.message === 'Permission denied' ? '暂无操作权限' : (error?.message ?? '调课时间冲突')
  }
  if (error?.message === 'Permission denied') {
    return '暂无操作权限'
  }

  return error?.message ?? '运营操作失败'
}

export function buildDashboardChartParams(input: { tenant_id?: number, campus_id?: number, start_at?: string, end_at?: string }): Record<string, unknown> {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    start_at: input.start_at,
    end_at: input.end_at,
  }
}
