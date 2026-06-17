import { describe, expect, it } from 'vitest'
import { disputeReviewPayload } from '../payrollRules.ts'

describe('workload dispute list', () => {
  it('review_drawer_records_approve_reject_note', () => {
    expect(disputeReviewPayload('approved', 'counted')).toEqual({ status: 'approved', review_note: 'counted' })
    expect(disputeReviewPayload('rejected', 'duplicate')).toEqual({ status: 'rejected', review_note: 'duplicate' })
  })
})
