import { describe, expect, it } from 'vitest'
import { normalizeTeacherSelection } from '../courseAccountRules.ts'

describe('course teacher drawer', () => {
  it('loads_and_saves_teacher_authorizations', () => {
    expect(normalizeTeacherSelection([201, '202', 201, 0, Number.NaN])).toEqual([201, 202])
  })
})
