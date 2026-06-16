import { describe, expect, it } from 'vitest'
import {
  calendarEventSummary,
  calendarQuery,
  conflictDrawerRows,
} from '../classScheduleRules.ts'

describe('lesson schedule calendar', () => {
  it('renders_calendar_events', () => {
    expect(calendarEventSummary({
      title: 'Sketch 01',
      start_at: '2026-06-16 09:00:00',
      end_at: '2026-06-16 10:00:00',
      class_name_snapshot: 'A班',
      teacher_name_snapshot: 'Lin',
      classroom_name_snapshot: 'Room 8',
      status: 'scheduled',
    })).toEqual('Sketch 01 · Lin · Room 8')
  })

  it('conflict_check_opens_conflict_drawer', () => {
    expect(conflictDrawerRows({
      has_conflict: true,
      conflicts: [
        { conflict_type: 'teacher', lesson_ids: [10, 11] },
      ],
    })).toEqual([
      { conflict_type: 'teacher', lesson_id: 10 },
      { conflict_type: 'teacher', lesson_id: 11 },
    ])
  })

  it('keeps_calendar_range_in_query', () => {
    expect(calendarQuery({
      campus_id: 1,
      class_id: 2,
      teacher_id: 3,
      classroom_id: 4,
      status: 'scheduled',
      start_at: '2026-06-01 00:00:00',
      end_at: '2026-06-30 23:59:59',
      ignored: 'value',
    })).toEqual({
      campus_id: 1,
      class_id: 2,
      teacher_id: 3,
      classroom_id: 4,
      status: 'scheduled',
      start_at: '2026-06-01 00:00:00',
      end_at: '2026-06-30 23:59:59',
    })
  })
})
