import { readFileSync } from 'fs'
import { join } from 'path'

describe('guardian leave create page', () => {
  it('leave_create_requires_reason', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/leave/create.vue'), 'utf8')

    expect(source).toContain('createGuardianLeave')
    expect(source).toContain('Reason is required')
    expect(source).toContain('state.submitting')
    expect(source).toContain('makeup_required: state.makeupRequired')
  })

  it('duplicate_leave_conflict_state', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/leave/create.vue'), 'utf8')

    expect(source).toContain("state.status = isConflict(error) ? 'conflict'")
    expect(source).toContain('code?: number })?.code === 409')
    expect(source).toContain('/pages/guardian/schedule/index?studentId=')
  })
})
