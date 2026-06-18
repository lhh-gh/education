import { readFileSync } from 'fs'
import { join } from 'path'

describe('teacher leave detail page contract', () => {
  const source = readFileSync(join(process.cwd(), 'src/pages/teacher/leave/detail.vue'), 'utf8')

  it('requires_review_remark_before_approve_or_reject', () => {
    expect(source).toContain("state.reviewRemark.trim() === ''")
    expect(source).toContain("state.message = 'review_remark is required'")
    expect(source).toContain('approveTeacherLeaveRequest')
    expect(source).toContain('rejectTeacherLeaveRequest')
  })

  it('shows_conflict_state_and_refresh_action', () => {
    expect(source).toContain("code === 409 ? 'conflict'")
    expect(source).toContain("state.status === 'conflict'")
    expect(source).toContain('@tap="retry"')
  })

  it('submitting_state_disables_review_actions', () => {
    expect(source).toContain('if (state.submitting)')
    expect(source).toContain(':disabled="state.submitting"')
  })
})
