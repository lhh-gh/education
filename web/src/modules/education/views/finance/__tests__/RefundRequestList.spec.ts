import { describe, expect, it } from 'vitest'
import { validateRefundAmount } from '../financeRules.ts'

describe('refund request list', () => {
  it('refund_approval_drawer_blocks_amount_over_refundable_amount', () => {
    expect(validateRefundAmount(60001, 60000)).toBe('Refund amount exceeds refundable amount')
    expect(validateRefundAmount(60000, 60000)).toBe('')
  })
})
