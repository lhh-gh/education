import { describe, expect, it } from 'vitest'
import { tenantActionsByPermission } from '../actionRules.ts'

describe('TenantList', () => {
  it('renders tenant actions_by_permission', () => {
    const enabledActions = tenantActionsByPermission([
      'education:foundation:tenant:create',
      'education:foundation:tenant:status',
    ], 'enabled')

    expect(enabledActions.canCreate).toBe(true)
    expect(enabledActions.canEdit).toBe(false)
    expect(enabledActions.statusAction).toBe('disable')

    const disabledActions = tenantActionsByPermission([
      'education:foundation:tenant:status',
    ], 'disabled')

    expect(disabledActions.statusAction).toBe('enable')
  })
})
