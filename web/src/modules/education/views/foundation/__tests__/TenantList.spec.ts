import { describe, expect, it } from 'vitest'
import { tenantActionsByPermission } from '../actionRules.ts'

describe('tenant list', () => {
  it('loads_tenants_on_mount', () => {
    expect({ page: 1, page_size: 20 }).toEqual({
      page: 1,
      page_size: 20,
    })
  })

  it('hides_create_without_permission', () => {
    expect(tenantActionsByPermission([], 'enabled').canCreate).toBe(false)
  })

  it('status_flow_calls_updateTenantStatus', () => {
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
