import { describe, expect, it } from 'vitest'
import {
  classActionsByPermission,
  classFormStateAfterFailure,
  classQuery,
} from '../classScheduleRules.ts'

describe('class list', () => {
  it('renders_class_table_and_filters', () => {
    expect(classQuery({
      page: 2,
      page_size: 50,
      tenant_id: 1001,
      campus_id: 2001,
      course_id: 3001,
      main_teacher_id: 4001,
      keyword: 'Painting',
      status: 'enabled',
      ignored: 'value',
    })).toEqual({
      page: 2,
      page_size: 50,
      tenant_id: 1001,
      campus_id: 2001,
      course_id: 3001,
      main_teacher_id: 4001,
      keyword: 'Painting',
      status: 'enabled',
    })
  })

  it('permission_buttons_are_hidden_without_permission', () => {
    const actions = classActionsByPermission([
      'education:academic:class:update',
      'education:academic:class-student:page',
    ], 'enabled')

    expect(actions.canCreate).toBe(false)
    expect(actions.canEdit).toBe(true)
    expect(actions.canDelete).toBe(false)
    expect(actions.canStudentPage).toBe(true)
    expect(actions.canStudentSave).toBe(false)
    expect(actions.statusAction).toBe('disable')
  })

  it('duplicate_class_error_keeps_form_open', () => {
    expect(classFormStateAfterFailure({ code: 409, message: 'duplicate class code' })).toEqual({
      visible: true,
      message: 'duplicate class code',
    })
  })
})
