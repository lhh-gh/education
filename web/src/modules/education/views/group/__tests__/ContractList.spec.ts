import { describe, expect, it } from 'vitest'
import { activeContractRiskWarning } from '../groupRules.ts'

describe('contract list', () => {
  it('active_contract_high_risk_change_shows_risk_warning', () => {
    expect(activeContractRiskWarning({ status: 'active', amount_cents: 1000, risk_level: 'high' }, { amount_cents: 1200 })).toBe('Active contract amount changed')
    expect(activeContractRiskWarning({ status: 'draft', amount_cents: 1000, risk_level: 'high' }, { amount_cents: 1200 })).toBe('')
  })
})
