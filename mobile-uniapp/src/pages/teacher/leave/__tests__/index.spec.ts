import { readFileSync } from 'fs'
import { join } from 'path'

describe('teacher leave list page contract', () => {
  const source = readFileSync(join(process.cwd(), 'src/pages/teacher/leave/index.vue'), 'utf8')
  const badgeSource = readFileSync(join(process.cwd(), 'src/pages/teacher/components/LeaveStatusBadge.vue'), 'utf8')

  it('defaults_to_pending_and_supports_status_tabs', () => {
    expect(source).toContain("filter: 'pending'")
    expect(source).toContain("const statuses: TeacherLeaveStatus[] = ['pending', 'approved', 'rejected']")
    expect(source).toContain('selectStatus(status)')
  })

  it('supports_pull_refresh_bottom_loading_and_detail_route', () => {
    expect(source).toContain('onPullDownRefresh(refresh)')
    expect(source).toContain('onReachBottom(loadMore)')
    expect(source).toContain('state.loadingMore')
    expect(source).toContain('/pages/teacher/leave/detail?id=')
  })

  it('leave_status_badge_has_stable_dimensions', () => {
    expect(badgeSource).toContain('min-width: 160rpx')
    expect(badgeSource).toContain('border-radius: 8rpx')
    expect(badgeSource).toContain('.pending')
  })
})
