export interface ReportDateRangeValue {
  tenant_id?: number
  campus_id?: number
  start_at?: string
  end_at?: string
}

export type ReportState = 'loading' | 'empty' | 'error' | 'forbidden'

export function validateReportDateRange(value: ReportDateRangeValue, requireRange = false, maxDays = 366): string {
  if (requireRange && (!value.start_at || !value.end_at)) {
    return 'Date range is required'
  }
  if (!value.start_at || !value.end_at) {
    return ''
  }
  const startAt = new Date(value.start_at.replace(' ', 'T'))
  const endAt = new Date(value.end_at.replace(' ', 'T'))
  if (Number.isNaN(startAt.getTime()) || Number.isNaN(endAt.getTime())) {
    return 'Date range is invalid'
  }
  if (endAt.getTime() <= startAt.getTime()) {
    return 'End time must be after start time'
  }
  const days = (endAt.getTime() - startAt.getTime()) / 86_400_000
  if (days > maxDays) {
    return `Date range cannot exceed ${maxDays} days`
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
    loading: 'Loading report',
    empty: 'No report data',
    error: 'Report loading failed',
    forbidden: 'Permission required',
  }

  return messages[state]
}

export function shouldEmitRetry(state: ReportState): boolean {
  return state === 'error' || state === 'forbidden' || state === 'empty'
}

function formatReportDateTime(value: Date): string {
  const pad = (input: number) => String(input).padStart(2, '0')

  return `${value.getFullYear()}-${pad(value.getMonth() + 1)}-${pad(value.getDate())} ${pad(value.getHours())}:${pad(value.getMinutes())}:${pad(value.getSeconds())}`
}
