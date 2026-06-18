import { describe, expect, it } from 'vitest'
import {
  classStudentDrawerStateAfterFailure,
  classStudentSavePayload,
} from '../classScheduleRules.ts'

describe('class student drawer', () => {
  it('loads_and_saves_class_students', () => {
    expect(classStudentSavePayload([3, '4', 3, 0, 'bad'])).toEqual({
      students: [
        { student_id: 3 },
        { student_id: 4 },
      ],
    })
  })

  it('missing_account_error_keeps_drawer_open', () => {
    expect(classStudentDrawerStateAfterFailure({ code: 422, message: 'missing active course account' })).toEqual({
      visible: true,
      message: 'missing active course account',
    })
  })
})
