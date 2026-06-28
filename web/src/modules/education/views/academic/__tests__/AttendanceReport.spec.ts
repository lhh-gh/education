import { describe, expect, it } from 'vitest'
import { reportHasRows, summaryMetricItems, validateReportDateRange } from '../reportRules.ts'

describe('attendance report page', () => {
  it('filters_reload_attendance_report', () => {
    expect(validateReportDateRange({
      start_at: '2026-06-01 00:00:00',
      end_at: '2026-06-30 23:59:59',
    }, true)).toBe('')
  })

  it('empty_attendance_report_state', () => {
    expect(reportHasRows(0, 0)).toBe(false)
    expect(summaryMetricItems({ present_count: 1 }, ['present_count'])).toEqual([{ title: '出勤', value: 1 }])
  })
})
