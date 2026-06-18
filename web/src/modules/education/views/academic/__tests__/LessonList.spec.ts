import { describe, expect, it } from 'vitest'
import {
  lessonDetailStudentRows,
  lessonQuery,
  lessonRowsAfterCancel,
  lessonRowsAfterDeleteFailure,
} from '../classScheduleRules.ts'

describe('lesson list', () => {
  it('renders_lesson_rows_and_detail_drawer', () => {
    expect(lessonDetailStudentRows({
      students: [
        { student_name_snapshot: 'Alice', status: 'planned' },
      ],
    })).toEqual([
      { student_name_snapshot: 'Alice', status: 'planned' },
    ])
  })

  it('cancel_success_updates_row_status', () => {
    expect(lessonRowsAfterCancel([{ id: 8, status: 'scheduled' }], { id: 8, status: 'cancelled' })).toEqual([
      { id: 8, status: 'cancelled' },
    ])
  })

  it('delete_reference_failure_keeps_row', () => {
    expect(lessonRowsAfterDeleteFailure([{ id: 8, status: 'scheduled' }], { code: 409 })).toEqual([
      { id: 8, status: 'scheduled' },
    ])
  })

  it('renders_lesson_filters', () => {
    expect(lessonQuery({
      page: 1,
      page_size: 20,
      campus_id: 1,
      class_id: 2,
      course_id: 3,
      teacher_id: 4,
      classroom_id: 5,
      status: 'scheduled',
      start_at: '2026-06-01 00:00:00',
      end_at: '2026-06-30 23:59:59',
      keyword: 'Sketch',
      ignored: 'value',
    })).toEqual({
      page: 1,
      page_size: 20,
      campus_id: 1,
      class_id: 2,
      course_id: 3,
      teacher_id: 4,
      classroom_id: 5,
      status: 'scheduled',
      start_at: '2026-06-01 00:00:00',
      end_at: '2026-06-30 23:59:59',
      keyword: 'Sketch',
    })
  })
})
