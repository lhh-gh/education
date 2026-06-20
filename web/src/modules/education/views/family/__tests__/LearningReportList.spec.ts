import { describe, expect, it } from 'vitest'
import { guardianVisibleMarker, reportStatusAfterWithdraw } from '../familyRules.ts'

describe('learning report list', () => {
  it('asserts_withdrawn_report_status_hides_guardian_visible_marker', () => {
    expect(guardianVisibleMarker({ status: 'published' })).toBe('家长可见')
    expect(guardianVisibleMarker({ status: 'withdrawn' })).toBe('')
    expect(reportStatusAfterWithdraw({ id: 1, status: 'published' })).toBe('已撤回')
  })
})
