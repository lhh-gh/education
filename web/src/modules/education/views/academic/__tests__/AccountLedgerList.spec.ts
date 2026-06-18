import { describe, expect, it } from 'vitest'
import {
  accountStatusAction,
  ledgerRowsBySource,
} from '../courseAccountRules.ts'

describe('account ledger list', () => {
  it('renders_account_balances', () => {
    expect(accountStatusAction('active')).toBe('freeze')
    expect(accountStatusAction('frozen')).toBe('unfreeze')
    expect(accountStatusAction('closed')).toBe('closed')
  })

  it('ledger_drawer_renders_enrollment_rows', () => {
    const rows = ledgerRowsBySource([
      { source_type: 'enrollment', source_no: 'ENR001', units: '24.00' },
      { source_type: 'consumption', source_no: 'CLS001', units: '1.00' },
    ], 'enrollment')

    expect(rows).toEqual([{ source_type: 'enrollment', source_no: 'ENR001', units: '24.00' }])
  })
})
