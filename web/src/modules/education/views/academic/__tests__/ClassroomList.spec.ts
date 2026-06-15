import { describe, expect, it } from 'vitest'
import {
  classroomQuery,
  recordActionsByPermission,
} from '../actionRules.ts'

describe('classroom list', () => {
  it('passes_tenant_campus_keyword_and_status_filters', () => {
    expect(classroomQuery({
      page: 2,
      page_size: 50,
      tenant_id: 1001,
      campus_id: 2001,
      keyword: 'A101',
      status: 'enabled',
      ignored: 'value',
    })).toEqual({
      page: 2,
      page_size: 50,
      tenant_id: 1001,
      campus_id: 2001,
      keyword: 'A101',
      status: 'enabled',
    })
  })

  it('hides_delete_without_permission_and_resolves_status_action', () => {
    const disabledActions = recordActionsByPermission([
      'education:academic:classroom:update',
      'education:academic:classroom:status',
    ], 'classroom', 'enabled')

    expect(disabledActions.canDelete).toBe(false)
    expect(disabledActions.canEdit).toBe(true)
    expect(disabledActions.canStatus).toBe(true)
    expect(disabledActions.statusAction).toBe('disable')
  })
})
