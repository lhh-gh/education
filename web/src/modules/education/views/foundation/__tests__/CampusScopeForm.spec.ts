import { describe, expect, it } from 'vitest'
import { campusScopeSavePayload, campusScopeValidationError } from '../actionRules.ts'

describe('campus scope form', () => {
  it('saves selected campuses', () => {
    expect(campusScopeSavePayload([3, 1, 3, 2])).toEqual([1, 2, 3])
  })

  it('prevents submit when required teacher scope is empty', () => {
    expect(campusScopeValidationError('teacher', [])).toBe('campus scope is required')
    expect(campusScopeValidationError('guardian', [])).toBeNull()
  })
})
