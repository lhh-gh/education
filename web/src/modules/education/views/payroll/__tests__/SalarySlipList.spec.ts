import { describe, expect, it } from 'vitest'
import { adjustmentDrawerState } from '../payrollRules.ts'

describe('salary slip list', () => {
  it('adjustment_form_keeps_open_on_validation_failure_and_updates_payable_after_success', () => {
    expect(adjustmentDrawerState({ code: 422, payable_amount_cents: 120000 })).toEqual({ open: true, payableAmountCents: 120000 })
    expect(adjustmentDrawerState({ code: 200, payable_amount_cents: 125000 })).toEqual({ open: false, payableAmountCents: 125000 })
  })
})
