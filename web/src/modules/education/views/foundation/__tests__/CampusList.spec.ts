import { describe, expect, it } from 'vitest'
import { campusActionsByPermission, tenantRequired } from '../actionRules.ts'

describe('CampusList', () => {
  it('blocks campus actions_without_tenant', () => {
    const missingTenantActions = campusActionsByPermission([
      'education:foundation:campus:create',
    ], 'enabled')

    expect(tenantRequired()).toBe(true)
    expect(missingTenantActions.canCreate).toBe(false)
  })

  it('allows tenant scoped campus actions and status flow', () => {
    const actions = campusActionsByPermission([
      'education:foundation:campus:create',
      'education:foundation:campus:status',
    ], 'enabled', 7)

    expect(tenantRequired(7)).toBe(false)
    expect(actions.canCreate).toBe(true)
    expect(actions.statusAction).toBe('disable')
  })
})
