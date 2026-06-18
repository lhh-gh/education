import { describe, expect, it } from 'vitest'
import { versionActionState } from '../standardRules.ts'

describe('standard version list', () => {
  it('disables_publish_without_approved_review', () => {
    expect(versionActionState({ status: 'reviewing', review_status: 'pending' })).toEqual({ canPublish: false, badge: 'Review required' })
    expect(versionActionState({ status: 'reviewing', review_status: 'approved' })).toEqual({ canPublish: true, badge: 'Approved' })
  })
})
