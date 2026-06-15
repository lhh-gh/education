import { describe, expect, it } from 'vitest'
import {
  normalizePrimaryRelations,
  recordActionsByPermission,
  studentGuardianAction,
} from '../actionRules.ts'

describe('student list', () => {
  it('shows_guardian_action_only_with_student_guardian_save_permission', () => {
    expect(studentGuardianAction([])).toBe(false)
    expect(studentGuardianAction(['education:academic:student:update'])).toBe(false)
    expect(studentGuardianAction(['education:academic:student-guardian:save'])).toBe(true)
  })

  it('normalizes_relations_to_exactly_one_primary_contact', () => {
    const relations = normalizePrimaryRelations([
      { guardian_id: 301, relation: 'mother', is_primary: false },
      { guardian_id: 302, relation: 'father', is_primary: true },
      { guardian_id: 303, relation: 'guardian', is_primary: true },
    ])

    expect(relations.filter(item => item.is_primary)).toHaveLength(1)
    expect(relations[1].is_primary).toBe(true)
    expect(relations[2].is_primary).toBe(false)
  })

  it('allows_wildcard_permission_for_student_actions', () => {
    const actions = recordActionsByPermission(['education:*'], 'student', 'disabled')

    expect(actions.canCreate).toBe(true)
    expect(actions.canEdit).toBe(true)
    expect(actions.canStatus).toBe(true)
    expect(actions.canDelete).toBe(true)
    expect(actions.statusAction).toBe('enable')
  })
})
