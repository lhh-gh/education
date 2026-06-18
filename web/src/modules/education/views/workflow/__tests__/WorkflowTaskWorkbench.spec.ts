import { describe, expect, it } from 'vitest'
import { canCompleteTask, isOverdueTask } from '../workflowRules.ts'

describe('workflow task workbench', () => {
  it('hides_complete_for_unassigned_user_and_marks_overdue', () => {
    expect(canCompleteTask({ assignee_user_ids: [7], status: 'pending' }, 8)).toBe(false)
    expect(canCompleteTask({ assignee_user_ids: [7], status: 'pending' }, 7)).toBe(true)
    expect(canCompleteTask({ assignee_user_ids: [7], status: 'completed' }, 7)).toBe(false)
    expect(isOverdueTask({ status: 'overdue' })).toBe(true)
    expect(isOverdueTask({ status: 'pending', due_at: '2026-06-10 10:00:00' }, '2026-06-10 11:00:00')).toBe(true)
  })
})
