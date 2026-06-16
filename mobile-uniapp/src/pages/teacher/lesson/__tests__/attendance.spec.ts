import { readFileSync } from 'fs'
import { join } from 'path'

describe('teacher attendance page contract', () => {
  const source = readFileSync(join(process.cwd(), 'src/pages/teacher/lesson/attendance.vue'), 'utf8')
  const rowSource = readFileSync(join(process.cwd(), 'src/pages/teacher/components/AttendanceStudentRow.vue'), 'utf8')

  it('applies_leave_defaults_to_no_consume_zero_units', () => {
    expect(source).toContain("record.default_attendance_status")
    expect(source).toContain("record.default_consume_policy")
    expect(source).toContain("record.default_consumed_units")
    expect(source).toContain("record.consume_policy = 'no_consume'")
    expect(source).toContain("record.consumed_units = '0.00'")
  })

  it('prevents_duplicate_submit_while_submitting', () => {
    expect(source).toContain('if (state.submitting)')
    expect(source).toContain(':disabled="state.submitting"')
    expect(source).toContain("state.submitting ? 'Submitting' : 'Submit attendance'")
  })

  it('shows_conflict_reload_result_action', () => {
    expect(source).toContain("state.status = 'conflict'")
    expect(source).toContain('reloadResult')
    expect(source).toContain('getTeacherAttendanceResult')
  })

  it('student_row_has_fixed_status_controls', () => {
    expect(rowSource).toContain("['present', 'late', 'absent', 'leave']")
    expect(rowSource).toContain('grid-template-columns: repeat(4')
    expect(rowSource).toContain('min-height: 156rpx')
  })
})
