import { describe, expect, it } from 'vitest'
import { canSelectRuleForPreview, centsToYuan } from '../payrollRules.ts'

describe('salary rule list', () => {
  it('disabled_rule_cannot_be_selected_for_calculation_preview', () => {
    expect(canSelectRuleForPreview({ status: 'disabled' })).toBe(false)
    expect(canSelectRuleForPreview({ status: 'enabled' })).toBe(true)
    expect(centsToYuan(125000)).toBe('¥1250.00')
  })
})
