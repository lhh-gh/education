import { describe, expect, it } from 'vitest'
import { teacherProfileSelectorParams } from '../../../api/academic/profile.ts'
import { recordActionsByPermission } from '../actionRules.ts'

describe('teacher list', () => {
  it('loads_only_enabled_teacher_profiles_for_selector', () => {
    expect(teacherProfileSelectorParams(1001)).toEqual({
      tenant_id: 1001,
      role_code: 'teacher',
      status: 'enabled',
    })
  })

  it('uses_teacher_permissions_for_status_and_delete_actions', () => {
    const actions = recordActionsByPermission([
      'education:academic:teacher:status',
    ], 'teacher', 'disabled')

    expect(actions.canCreate).toBe(false)
    expect(actions.canEdit).toBe(false)
    expect(actions.canStatus).toBe(true)
    expect(actions.canDelete).toBe(false)
    expect(actions.statusAction).toBe('enable')
  })
})
