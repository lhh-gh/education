export interface ReportDateRangeValue {
  tenant_id?: number
  campus_id?: number
  start_at?: string
  end_at?: string
}

export type ReportState = 'loading' | 'empty' | 'error' | 'forbidden'

export function validateReportDateRange(value: ReportDateRangeValue, requireRange = false, maxDays = 366): string {
  if (requireRange && (!value.start_at || !value.end_at)) {
    return '请选择日期范围'
  }
  if (!value.start_at || !value.end_at) {
    return ''
  }
  const startAt = new Date(value.start_at.replace(' ', 'T'))
  const endAt = new Date(value.end_at.replace(' ', 'T'))
  if (Number.isNaN(startAt.getTime()) || Number.isNaN(endAt.getTime())) {
    return '日期范围无效'
  }
  if (endAt.getTime() <= startAt.getTime()) {
    return '结束时间必须晚于开始时间'
  }
  const days = (endAt.getTime() - startAt.getTime()) / 86_400_000
  if (days > maxDays) {
    return `日期范围不能超过 ${maxDays} 天`
  }

  return ''
}

export function quickReportRange(type: 'today' | 'this_week' | 'this_month', now = new Date()): Pick<ReportDateRangeValue, 'start_at' | 'end_at'> {
  const start = new Date(now)
  const end = new Date(now)
  if (type === 'today') {
    start.setHours(0, 0, 0, 0)
    end.setHours(23, 59, 59, 0)
  }
  if (type === 'this_week') {
    const day = start.getDay() || 7
    start.setDate(start.getDate() - day + 1)
    start.setHours(0, 0, 0, 0)
    end.setTime(start.getTime())
    end.setDate(start.getDate() + 6)
    end.setHours(23, 59, 59, 0)
  }
  if (type === 'this_month') {
    start.setDate(1)
    start.setHours(0, 0, 0, 0)
    end.setMonth(start.getMonth() + 1, 0)
    end.setHours(23, 59, 59, 0)
  }

  return {
    start_at: formatReportDateTime(start),
    end_at: formatReportDateTime(end),
  }
}

export function reportStateMessage(state: ReportState, message?: string): string {
  if (message) {
    return message
  }
  const messages: Record<ReportState, string> = {
    loading: '报表加载中',
    empty: '暂无报表数据',
    error: '报表加载失败',
    forbidden: '暂无查看权限',
  }

  return messages[state]
}

export function shouldEmitRetry(state: ReportState): boolean {
  return state === 'error' || state === 'forbidden' || state === 'empty'
}

export function reportHasRows(total = 0, listLength = 0): boolean {
  return total > 0 || listLength > 0
}

export function reportTagType(value?: string | null): '' | 'success' | 'warning' | 'danger' | 'info' {
  if (!value) {
    return ''
  }
  if (['pass', 'present', 'active', 'approved', 'normal', 'read', 'completed'].includes(value)) {
    return 'success'
  }
  if (['pending', 'late', 'low', 'expiring_soon', 'makeup_scheduled', 'warning'].includes(value)) {
    return 'warning'
  }
  if (['fail', 'absent', 'zero', 'expired', 'rejected', 'cancelled', 'danger'].includes(value)) {
    return 'danger'
  }
  if (['leave', 'reversed', 'frozen', 'closed'].includes(value)) {
    return 'info'
  }

  return ''
}

export function reportLevelLabel(value?: string | null): string {
  const labels: Record<string, string> = {
    normal: '正常',
    warning: '预警',
    danger: '高风险',
    low: '低',
    medium: '中',
    high: '高',
  }

  return value ? labels[value] ?? value : '未知'
}

export function canShowReportDrillLink(hasPermission: boolean, id?: number | null): boolean {
  return hasPermission && !!id
}

export function dashboardMetricItems(metrics: Record<string, string | number> = {}): Array<{ title: string, value: string | number, unit?: string }> {
  return [
    { title: '在读学员', value: metrics.active_student_count ?? 0 },
    { title: '已结课次', value: metrics.completed_lesson_count ?? 0 },
    { title: '出勤人次', value: metrics.attendance_count ?? 0 },
    { title: '净课消', value: metrics.net_consumed_units ?? '0.00', unit: '课时' },
    { title: '可用课时', value: metrics.total_available_units ?? '0.00', unit: '课时' },
    { title: '待处理请假', value: metrics.pending_leave_count ?? 0 },
    { title: '未读通知', value: metrics.unread_notice_receipt_count ?? 0 },
  ]
}

export function summaryMetricItems(summary: Record<string, string | number> = {}, keys: string[]): Array<{ title: string, value: string | number }> {
  return keys.map(key => ({
    title: reportMetricLabel(key),
    value: summary[key] ?? 0,
  }))
}

export function acceptanceOverallType(status: 'pass' | 'fail'): 'success' | 'danger' {
  return status === 'pass' ? 'success' : 'danger'
}

export function reportMetricLabel(key: string): string {
  const labels: Record<string, string> = {
    total_records: '记录数',
    present_count: '出勤',
    late_count: '迟到',
    absent_count: '缺勤',
    leave_count: '请假',
    attendance_rate: '出勤率',
    decrease_units: '扣减课时',
    rollback_units: '回滚课时',
    net_units: '净课消',
    active_row_count: '有效记录',
    reversed_row_count: '回滚记录',
    total_count: '总数',
    pending_count: '待审批',
    approved_count: '已通过',
    rejected_count: '已拒绝',
    cancelled_count: '已取消',
    makeup_scheduled_count: '已安排补课',
    guardian_source_count: '家长提交',
    account_count: '账户数',
    active_count: '正常账户',
    total_purchased_units: '购买课时',
    total_consumed_units: '已消课时',
    total_available_units: '可用课时',
    low_balance_count: '低课时账户',
    expiring_count: '即将过期',
  }

  return labels[key] ?? key
}

export function passFailLabel(status?: string | null): string {
  if (status === 'pass') {
    return '通过'
  }
  if (status === 'fail') {
    return '未通过'
  }

  return status ?? '未知'
}

function formatReportDateTime(value: Date): string {
  const pad = (input: number) => String(input).padStart(2, '0')

  return `${value.getFullYear()}-${pad(value.getMonth() + 1)}-${pad(value.getDate())} ${pad(value.getHours())}:${pad(value.getMinutes())}:${pad(value.getSeconds())}`
}
