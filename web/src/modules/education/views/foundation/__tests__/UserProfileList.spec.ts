import { describe, expect, it } from 'vitest'
import { educationRoleLabel, educationRoleOptions, foundationStatusLabel, userProfileActionsByPermission } from '../actionRules.ts'

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

  it('uses_chinese_role_and_status_labels', () => {
    expect(educationRoleLabel('platform_super_admin')).toBe('平台超级管理员')
    expect(educationRoleLabel('academic_staff')).toBe('教务')
    expect(educationRoleOptions().map(item => item.label)).toContain('教师')
    expect(foundationStatusLabel('enabled')).toBe('启用')
    expect(foundationStatusLabel('disabled')).toBe('停用')
  })
})
