import { describe, expect, it } from 'vitest'
import { canApproveAiResult } from '../aiRules.ts'

describe('ai review list', () => {
  it('disables_approve_for_safety_blocked_result', () => {
    expect(canApproveAiResult({ safety_status: 'normal', review_status: 'pending' })).toBe(true)
    expect(canApproveAiResult({ safety_status: 'blocked', review_status: 'pending' })).toBe(false)
    expect(canApproveAiResult({ safety_status: 'normal', review_status: 'approved' })).toBe(false)
  })
})
