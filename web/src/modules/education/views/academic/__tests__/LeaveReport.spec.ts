import { describe, expect, it } from 'vitest'
import { reportTagType, summaryMetricItems } from '../reportRules.ts'

describe('leave report page', () => {
  it('renders_leave_summary_and_rows', () => {
    expect(summaryMetricItems({ pending_count: 3, approved_count: 4 }, ['pending_count', 'approved_count'])).toEqual([
      { title: '待审批', value: 3 },
      { title: '已通过', value: 4 },
    ])
    expect(reportTagType('pending')).toBe('warning')
    expect(reportTagType('approved')).toBe('success')
  })
})
