import { describe, expect, it } from 'vitest'
import { canShowReportDrillLink, reportTagType } from '../reportRules.ts'

describe('account balance report page', () => {
  it('renders_balance_levels', () => {
    expect(reportTagType('low')).toBe('warning')
    expect(reportTagType('expired')).toBe('danger')
    expect(reportTagType('normal')).toBe('success')
  })

  it('account_ledger_link_requires_permission', () => {
    expect(canShowReportDrillLink(false, 601)).toBe(false)
    expect(canShowReportDrillLink(true, 601)).toBe(true)
  })
})
