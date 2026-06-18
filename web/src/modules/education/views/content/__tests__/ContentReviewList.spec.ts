import { describe, expect, it } from 'vitest'
import { reviewSubmitState } from '../contentRules.ts'

describe('content review list', () => {
  it('asserts_approve_reject_state_and_review_note_validation', () => {
    expect(reviewSubmitState({ status: 'approved' })).toEqual({ disabled: false, message: 'Ready' })
    expect(reviewSubmitState({ status: 'rejected' })).toEqual({ disabled: true, message: 'Review note is required when rejecting' })
    expect(reviewSubmitState({ status: 'rejected', review_note: 'missing license' })).toEqual({ disabled: false, message: 'Ready' })
  })
})
