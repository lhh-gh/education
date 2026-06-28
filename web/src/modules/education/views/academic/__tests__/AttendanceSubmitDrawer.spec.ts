import { describe, expect, it } from 'vitest'
import {
  accountBalanceWarning,
  attendanceResultSummary,
  defaultAttendanceRecords,
  uniqueAttendanceSummaryRows,
} from '../attendanceConsumptionRules.ts'

describe('attendance submit drawer', () => {
  it('loads_students_and_submits_attendance', () => {
    const records = defaultAttendanceRecords([
      { id: 11, student_id: 21, student_name_snapshot: 'Student A', account_id: 31, lesson_units: '1.50' },
    ])

    expect(records).toEqual([
      { lesson_student_id: 11, attendance_status: 'present', consume_policy: 'consume', consumed_units: 1.5, remark: '' },
    ])
  })

  it('insufficient_balance_highlights_student_row', () => {
    const warning = accountBalanceWarning({
      id: 11,
      student_id: 21,
      student_name_snapshot: 'Student A',
      account_id: 31,
      lesson_units: '2.00',
      account_available_units: '0.50',
    }, 2)

    expect(warning).toBe('可用课时不足')
  })

  it('idempotent_success_does_not_duplicate_result_rows', () => {
    const rows = uniqueAttendanceSummaryRows([{ id: 1 }, { id: 1 }, { lesson_student_id: 2 }])

    expect(rows).toEqual([{ id: 1 }, { lesson_student_id: 2 }])
    expect(attendanceResultSummary({ attendance_count: 2, consumed_count: 1, total_consumed_units: '1.00' })).toBe('2/1/1.00')
  })
})
