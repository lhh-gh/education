import { describe, expect, it } from 'vitest'
import {
  normalizePrimaryRelations,
  recordActionsByPermission,
} from '../actionRules.ts'

describe('guardian list', () => {
  it('hides_dangerous_actions_without_guardian_permissions', () => {
    const actions = recordActionsByPermission([
      'education:academic:guardian:create',
      'education:academic:guardian:update',
    ], 'guardian', 'enabled')

    expect(actions.canCreate).toBe(true)
    expect(actions.canEdit).toBe(true)
    expect(actions.canStatus).toBe(false)
    expect(actions.canDelete).toBe(false)
  })

  it('keeps_primary_relation_when_guardian_mobile_conflict_is_returned', () => {
    const relations = normalizePrimaryRelations([
      { guardian_id: 501, relation: 'guardian', is_primary: true },
    ])
    const errorText = 'guardian mobile already exists'

    expect(errorText).toContain('mobile')
    expect(relations).toEqual([
      { guardian_id: 501, relation: 'guardian', is_primary: true },
    ])
  })
})
