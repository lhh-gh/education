import { readFileSync } from 'fs'
import { join } from 'path'

describe('guardian consumption page', () => {
  it('consumption_rows_render_direction_and_status', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/consumption/index.vue'), 'utf8')

    expect(source).toContain('pageGuardianStudentConsumptions')
    expect(source).toContain('row.direction')
    expect(source).toContain('row.units')
    expect(source).toContain("row.status === 'reversed'")
    expect(source).toContain('account_id: state.accountId')
  })
})
