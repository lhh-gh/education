import { describe, expect, it } from 'vitest'
import { canCompleteApprovalTask } from '../groupRules.ts'

describe('approval task list', () => {
  it('only_assigned_or_override_users_see_complete_button', () => {
    expect(canCompleteApprovalTask({ assignee_user_id: 88, status: 'pending' }, 88, false)).toBe(true)
    expect(canCompleteApprovalTask({ assignee_user_id: 88, status: 'pending' }, 99, false)).toBe(false)
    expect(canCompleteApprovalTask({ assignee_user_id: 88, status: 'pending' }, 99, true)).toBe(true)
    expect(canCompleteApprovalTask({ assignee_user_id: 88, status: 'completed' }, 88, true)).toBe(false)
  })
})
