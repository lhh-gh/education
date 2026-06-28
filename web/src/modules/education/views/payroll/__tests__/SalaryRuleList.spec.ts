import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'
import { canSelectRuleForPreview, centsToYuan } from '../payrollRules.ts'

const salaryRuleListSource = readFileSync(resolve(__dirname, '../SalaryRuleList.vue'), 'utf8')

describe('salary rule list', () => {
  it('disabled_rule_cannot_be_selected_for_calculation_preview', () => {
    expect(canSelectRuleForPreview({ status: 'disabled' })).toBe(false)
    expect(canSelectRuleForPreview({ status: 'enabled' })).toBe(true)
    expect(centsToYuan(125000)).toBe('¥1250.00')
  })

  it('guards_create_rule_action_with_backend_permission', () => {
    expect(salaryRuleListSource).toContain('import hasAuth from \'@/utils/permission/hasAuth.ts\'')
    expect(salaryRuleListSource).toContain('hasAuth(\'education:payroll:rule:create\')')
    expect(salaryRuleListSource).toContain('v-if="canCreate"')
  })
})
