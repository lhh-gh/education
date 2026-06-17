import { describe, expect, it } from 'vitest'
import { operationPermissions, operationTagType } from '../operationRules.ts'

describe('consumption review list', () => {
  it('approve_button_is_hidden_without_permission', () => {
    const permissions = operationPermissions(() => false)

    expect(permissions.approveConsumption).toBe(false)
  })

  it('pending_reviews_use_warning_badge', () => {
    expect(operationTagType('pending')).toBe('warning')
  })
})
