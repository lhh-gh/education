import { describe, expect, it } from 'vitest'
import {
  batchConflictCalendarRows,
  batchSchedulePayload,
  batchScheduleSuccessSummary,
} from '../classScheduleRules.ts'

describe('batch lesson schedule drawer', () => {
  it('batch_schedule_success_shows_batch_no', () => {
    expect(batchScheduleSuccessSummary({
      schedule_batch_no: 'B202606160001',
      created_count: 6,
    })).toBe('B202606160001 · created 6')
  })

  it('batch_conflict_persists_no_events_in_ui', () => {
    expect(batchConflictCalendarRows([{ id: 1 }, { id: 2 }], { has_conflict: true })).toEqual([{ id: 1 }, { id: 2 }])
  })

  it('builds_batch_schedule_payload', () => {
    expect(batchSchedulePayload({
      campus_id: 1,
      class_id: 2,
      teacher_id: 3,
      classroom_id: '',
      title_template: 'Sketch',
      start_date: '2026-06-16',
      end_date: '2026-06-30',
      weekdays: ['1', 3, 3],
      start_time: '09:00',
      end_time: '10:00',
      lesson_units: '1',
      remark: '',
    })).toEqual({
      campus_id: 1,
      class_id: 2,
      teacher_id: 3,
      classroom_id: null,
      title_template: 'Sketch',
      start_date: '2026-06-16',
      end_date: '2026-06-30',
      weekdays: [1, 3],
      start_time: '09:00',
      end_time: '10:00',
      lesson_units: 1,
      remark: null,
    })
  })
})
