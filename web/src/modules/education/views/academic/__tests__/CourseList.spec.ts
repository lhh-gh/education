import { describe, expect, it } from 'vitest'
import {
  courseActionsByPermission,
  courseQuery,
} from '../courseAccountRules.ts'

describe('course list', () => {
  it('renders_course_table_and_filters', () => {
    expect(courseQuery({
      page: 2,
      page_size: 50,
      tenant_id: 1001,
      campus_id: 2001,
      keyword: 'Art',
      status: 'enabled',
      ignored: 'value',
    })).toEqual({
      page: 2,
      page_size: 50,
      tenant_id: 1001,
      campus_id: 2001,
      keyword: 'Art',
      status: 'enabled',
    })
  })

  it('permission_buttons_are_hidden_without_permission', () => {
    const actions = courseActionsByPermission([
      'education:academic:course:update',
      'education:academic:course-teacher:page',
    ], 'enabled')

    expect(actions.canCreate).toBe(false)
    expect(actions.canEdit).toBe(true)
    expect(actions.canDelete).toBe(false)
    expect(actions.teacherAction).toBe(true)
    expect(actions.statusAction).toBe('disable')
  })
})
