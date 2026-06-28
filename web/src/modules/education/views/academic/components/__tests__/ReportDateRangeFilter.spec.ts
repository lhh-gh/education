import { describe, expect, it } from 'vitest'
import { quickReportRange, validateReportDateRange } from '../../reportRules.ts'

describe('report date range filter', () => {
  it('rejects_range_over_max_days', () => {
    expect(validateReportDateRange({
      start_at: '2026-01-01 00:00:00',
      end_at: '2027-01-03 00:00:00',
    }, true, 366)).toBe('日期范围不能超过 366 天')
  })

  it('builds_this_month_quick_range', () => {
    expect(quickReportRange('this_month', new Date('2026-06-17T12:00:00'))).toEqual({
      start_at: '2026-06-01 00:00:00',
      end_at: '2026-06-30 23:59:59',
    })
  })
})
