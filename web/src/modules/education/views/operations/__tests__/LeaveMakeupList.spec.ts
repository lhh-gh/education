import { describe, expect, it } from 'vitest'
import { operationTagType } from '../operationRules.ts'

describe('leave makeup list', () => {
  it('shows_expired_entitlement_as_danger', () => {
    expect(operationTagType('expired')).toBe('danger')
  })

  it('locks_used_entitlement_badge_as_success', () => {
    expect(operationTagType('used')).toBe('success')
  })
})
