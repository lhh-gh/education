import { describe, expect, it } from 'vitest'
import { dashboardMetricItems } from '../reportRules.ts'

describe('academic dashboard report page', () => {
  it('renders_metric_cards_and_alerts', () => {
    const metrics = dashboardMetricItems({
      active_student_count: 42,
      completed_lesson_count: 56,
      attendance_count: 112,
      net_consumed_units: '108.00',
      total_available_units: '820.00',
      pending_leave_count: 3,
      unread_notice_receipt_count: 5,
    })

    expect(metrics.map(item => item.title)).toContain('在读学员')
    expect(metrics.find(item => item.title === '净课消')).toMatchObject({ value: '108.00', unit: '课时' })
  })

  it('dashboard_forbidden_state', () => {
    expect(dashboardMetricItems({}).find(item => item.title === '在读学员')?.value).toBe(0)
  })
})
