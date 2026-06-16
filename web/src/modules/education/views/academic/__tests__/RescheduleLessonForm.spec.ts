import type { RescheduleLessonResult } from '../../../api/academic/lessonChange.ts'
import { describe, expect, it } from 'vitest'
import { conflictMessage, rescheduleSuccessSummary } from '../leaveMakeupRescheduleRules.ts'

describe('reschedule lesson form', () => {
  it('reschedule_success_updates_lesson_summary', () => {
    const result: RescheduleLessonResult = {
      lesson: { id: 9001, start_at: '2026-06-17 09:00:00', end_at: '2026-06-17 10:00:00' },
      change_record: { id: 2, tenant_id: 1, campus_id: 1, change_no: 'CHG002', change_type: 'reschedule', status: 'confirmed', source_lesson_id: 9001, target_lesson_id: 9001, class_id: 12, course_id: 13, lesson_units: '1.00', reason: 'move' },
    }

    expect(rescheduleSuccessSummary(result)).toBe('2026-06-17 09:00:00/2026-06-17 10:00:00')
  })

  it('teacher_conflict_keeps_form_open', () => {
    expect(conflictMessage({ data: { conflicts: [{ conflict_type: 'teacher', lesson_ids: [9009] }] } })).toBe('teacher: 9009')
  })
})
