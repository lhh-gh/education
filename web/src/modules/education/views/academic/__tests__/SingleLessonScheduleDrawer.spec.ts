import { describe, expect, it } from 'vitest'
import {
  scheduleDrawerStateAfterFailure,
  singleSchedulePayload,
  singleScheduleSuccessSummary,
} from '../classScheduleRules.ts'

describe('single lesson schedule drawer', () => {
  it('schedule_success_shows_lesson_summary', () => {
    expect(singleScheduleSuccessSummary({
      lesson: { lesson_no: 'L202606160001' },
      lesson_students: { created_count: 8 },
    })).toBe('L202606160001 · students 8')
  })

  it('teacher_conflict_keeps_drawer_open', () => {
    expect(scheduleDrawerStateAfterFailure({ code: 409, message: 'teacher conflict' })).toEqual({
      visible: true,
      message: 'teacher conflict',
    })
  })

  it('builds_single_schedule_payload', () => {
    expect(singleSchedulePayload({
      campus_id: 1,
      class_id: 2,
      teacher_id: 3,
      classroom_id: undefined,
      title: 'Sketch',
      start_at: '2026-06-16 09:00:00',
      end_at: '2026-06-16 10:00:00',
      lesson_units: '1.5',
      remark: '',
    })).toEqual({
      campus_id: 1,
      class_id: 2,
      teacher_id: 3,
      classroom_id: null,
      title: 'Sketch',
      start_at: '2026-06-16 09:00:00',
      end_at: '2026-06-16 10:00:00',
      lesson_units: 1.5,
      remark: null,
    })
  })
})
