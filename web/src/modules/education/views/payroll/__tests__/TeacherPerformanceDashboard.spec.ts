import { describe, expect, it } from 'vitest'
import { buildPerformanceParams } from '../payrollRules.ts'

describe('teacher performance dashboard', () => {
  it('campus_month_filters_are_sent_to_metrics_apis', () => {
    expect(buildPerformanceParams({ campus_id: 10, metric_month: '2026-06', teacher_id: 301 })).toEqual({
      campus_id: 10,
      metric_month: '2026-06',
      teacher_id: 301,
    })
  })
})
