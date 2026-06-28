import { describe, expect, it } from 'vitest'
import { reviewSubmitState } from '../contentRules.ts'

describe('content review list', () => {
  it('asserts_approve_reject_state_and_review_note_validation', () => {
    expect(reviewSubmitState({ status: 'approved' })).toEqual({ disabled: false, message: '可提交' })
    expect(reviewSubmitState({ status: 'rejected' })).toEqual({ disabled: true, message: '驳回时必须填写审核意见' })
    expect(reviewSubmitState({ status: 'rejected', review_note: 'missing license' })).toEqual({ disabled: false, message: '可提交' })
  })
})
