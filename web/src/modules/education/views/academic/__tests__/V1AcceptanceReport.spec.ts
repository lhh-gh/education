import { describe, expect, it } from 'vitest'
import { acceptanceOverallType, reportHasRows, reportTagType } from '../reportRules.ts'

describe('v1 acceptance report page', () => {
  it('renders_pass_gate_table', () => {
    expect(acceptanceOverallType('pass')).toBe('success')
    expect(reportTagType('pass')).toBe('success')
  })

  it('renders_ledger_mismatch_when_failed', () => {
    expect(acceptanceOverallType('fail')).toBe('danger')
    expect(reportTagType('fail')).toBe('danger')
    expect(reportHasRows(1, 1)).toBe(true)
  })
})
