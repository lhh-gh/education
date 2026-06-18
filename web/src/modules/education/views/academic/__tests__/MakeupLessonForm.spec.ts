import type { MakeupLessonResult } from '../../../api/academic/lessonChange.ts'
import { describe, expect, it } from 'vitest'
import { conflictMessage, makeupSuccessSummary } from '../leaveMakeupRescheduleRules.ts'

describe('makeup lesson form', () => {
  it('makeup_success_shows_target_lesson', () => {
    const result: MakeupLessonResult = {
      leave_request: { id: 1, tenant_id: 1, campus_id: 1, leave_no: 'LEA001', source: 'staff', leave_type: 'sick', lesson_id: 10, lesson_student_id: 11, class_id: 12, course_id: 13, student_id: 14, account_id: 15, reason: 'Sick', status: 'makeup_scheduled', makeup_required: true },
      target_lesson: { id: 9101 },
      target_lesson_student: { id: 9201 },
      change_record: { id: 2, tenant_id: 1, campus_id: 1, change_no: 'CHG001', change_type: 'makeup', status: 'confirmed', source_lesson_id: 10, class_id: 12, course_id: 13, lesson_units: '1.00', reason: 'makeup' },
    }

    expect(makeupSuccessSummary(result)).toBe('9101/CHG001')
  })

  it('student_conflict_keeps_form_open', () => {
    expect(conflictMessage({ data: { conflicts: [{ conflict_type: 'student', lesson_ids: [9009] }] } })).toBe('student: 9009')
  })
})
