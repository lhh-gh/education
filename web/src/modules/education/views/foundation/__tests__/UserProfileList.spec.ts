import { describe, expect, it } from 'vitest'
import { userProfileActionsByPermission } from '../actionRules.ts'

describe('user profile list', () => {
  it('campus_scope_button_requires_permission', () => {
    expect(userProfileActionsByPermission([], 'enabled', 'teacher').canCampusScope).toBe(false)
    expect(userProfileActionsByPermission([
      'education:foundation:campus-scope:save',
    ], 'enabled', 'teacher').canCampusScope).toBe(true)
    expect(userProfileActionsByPermission([
      'education:foundation:campus-scope:save',
    ], 'enabled', 'platform_operator').canCampusScope).toBe(false)
  })

  it('status_flow_calls_updateUserProfileStatus', () => {
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
