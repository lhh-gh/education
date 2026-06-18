import { describe, expect, it } from 'vitest'
import { reconciliationRowState } from '../financeRules.ts'

describe('reconciliation batch list', () => {
  it('unmatched_rows_render_exception_state', () => {
    expect(reconciliationRowState({ match_status: 'unmatched' })).toBe('exception')
    expect(reconciliationRowState({ match_status: 'matched' })).toBe('normal')
  })
})
