import { describe, expect, it } from 'vitest'
import { guardianVisibleMarker, reportStatusAfterWithdraw } from '../familyRules.ts'

describe('learning report list', () => {
  it('asserts_withdrawn_report_status_hides_guardian_visible_marker', () => {
    expect(guardianVisibleMarker({ status: 'published' })).toBe('Guardian visible')
    expect(guardianVisibleMarker({ status: 'withdrawn' })).toBe('')
    expect(reportStatusAfterWithdraw({ id: 1, status: 'published' })).toBe('withdrawn')
  })
})
