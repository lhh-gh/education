import { readFileSync } from 'fs'
import { join } from 'path'

describe('guardian account page', () => {
  it('account_cards_render_balances', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/account/index.vue'), 'utf8')
    const card = readFileSync(join(process.cwd(), 'src/pages/guardian/components/AccountBalanceCard.vue'), 'utf8')

    expect(source).toContain('pageGuardianStudentAccounts')
    expect(source).toContain('AccountBalanceCard')
    expect(source).toContain('/pages/guardian/consumption/index?studentId=')
    expect(card).toContain('available_units')
    expect(card).toContain('consumed_units')
    expect(card).toContain('purchased_units')
  })
})
