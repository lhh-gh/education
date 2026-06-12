import { describe, expect, it } from 'vitest'
import { userProfileActionsByPermission } from '../actionRules.ts'

describe('user profile list', () => {
  it('renders actions by permission and profile status', () => {
    const enabledTeacherActions = userProfileActionsByPermission([
      'education:foundation:user-profile:create',
      'education:foundation:user-profile:status',
      'education:foundation:campus-scope:save',
    ], 'enabled', 'teacher')

    expect(enabledTeacherActions.canCreate).toBe(true)
    expect(enabledTeacherActions.canEdit).toBe(false)
    expect(enabledTeacherActions.statusAction).toBe('disable')
    expect(enabledTeacherActions.canCampusScope).toBe(true)

    const disabledPlatformActions = userProfileActionsByPermission([
      'education:foundation:user-profile:status',
      'education:foundation:campus-scope:save',
    ], 'disabled', 'platform_operator')

    expect(disabledPlatformActions.statusAction).toBe('enable')
    expect(disabledPlatformActions.canCampusScope).toBe(false)
  })
})
