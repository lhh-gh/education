import { describe, expect, it } from 'vitest'
import { duplicateCallbackText } from '../financeRules.ts'

describe('payment record list', () => {
  it('duplicate_callback_status_displays_payment_already_processed', () => {
    expect(duplicateCallbackText('payment already processed')).toBe('payment already processed')
  })
})
