import { describe, expect, it } from 'vitest'
import { canRebuildBatch, payrollConflictText } from '../payrollRules.ts'

describe('salary batch list', () => {
  it('rebuild_button_hides_after_submitted_status_and_409_state_displays', () => {
    expect(canRebuildBatch({ status: 'draft' })).toBe(true)
    expect(canRebuildBatch({ status: 'calculated' })).toBe(true)
    expect(canRebuildBatch({ status: 'submitted' })).toBe(false)
    expect(payrollConflictText('salary batch is not submitted')).toBe('薪酬批次未提交')
  })
})
