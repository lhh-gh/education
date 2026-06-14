import { describe, expect, it } from 'vitest'
import { campusScopeSavePayload, campusScopeValidationError } from '../actionRules.ts'

describe('campus scope form', () => {
  it('loads_scope_before_open', () => {
    expect({ profile_id: 3001, tenant_id: 1001 }).toMatchObject({
      profile_id: 3001,
      tenant_id: 1001,
    })
  })

  it('save_submits_selected_campus_ids', () => {
    expect(campusScopeSavePayload([3, 1, 3, 2])).toEqual([1, 2, 3])
  })

  it('prevents submit when required teacher scope is empty', () => {
    expect(campusScopeValidationError('teacher', [])).toBe('campus scope is required')
    expect(campusScopeValidationError('guardian', [])).toBeNull()
  })
})
