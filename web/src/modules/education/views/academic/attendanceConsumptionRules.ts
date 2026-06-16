import type {
  AccountAdjustmentRecord,
  AttendanceLessonStudentRecord,
  AttendanceSubmitRecord,
  AttendanceSubmitResult,
  ConsumptionRecord,
} from '../../api/academic/attendanceConsumption.ts'

export function canSubmitAttendance(status: string, hasPermission: boolean): boolean {
  return hasPermission && status === 'scheduled'
}

export function defaultAttendanceRecords(students: AttendanceLessonStudentRecord[]): AttendanceSubmitRecord[] {
  return students.map(student => ({
    lesson_student_id: student.lesson_student_id ?? student.id,
    attendance_status: 'present',
    consume_policy: 'consume',
    consumed_units: Number(student.lesson_units ?? student.planned_units ?? 1),
    remark: '',
  }))
}

export function accountBalanceWarning(student: AttendanceLessonStudentRecord, consumedUnits: number): string {
  const available = Number(student.account_available_units ?? Number.POSITIVE_INFINITY)
  return Number.isFinite(available) && available < consumedUnits ? 'Insufficient available units' : ''
}

export function uniqueAttendanceSummaryRows(rows: Array<{ id?: number, lesson_student_id?: number }>): Array<{ id?: number, lesson_student_id?: number }> {
  const seen = new Set<string>()
  return rows.filter((row) => {
    const key = String(row.id ?? row.lesson_student_id)
    if (seen.has(key)) {
      return false
    }
    seen.add(key)
    return true
  })
}

export function attendanceResultSummary(result: AttendanceSubmitResult): string {
  return `${result.attendance_count}/${result.consumed_count}/${result.total_consumed_units}`
}

export function markConsumptionRollbackSuccess(rows: ConsumptionRecord[], original: ConsumptionRecord): ConsumptionRecord[] {
  return rows.map(row => row.id === original.id ? { ...row, ...original, status: 'reversed' } : row)
}

export function canRollbackConsumption(row: ConsumptionRecord, hasPermission: boolean): boolean {
  return hasPermission && row.source_type === 'attendance' && row.status === 'active'
}

export function markAdjustmentRollbackSuccess(rows: AccountAdjustmentRecord[], original: AccountAdjustmentRecord): AccountAdjustmentRecord[] {
  return rows.map(row => row.id === original.id ? { ...row, ...original, status: 'rolled_back' } : row)
}

export function canRollbackAdjustment(row: AccountAdjustmentRecord, hasPermission: boolean): boolean {
  return hasPermission && row.adjustment_type === 'supplement_deduction' && row.status === 'confirmed'
}

export function buildSupplementDeductionPayload(accountId: number, units: number, reason: string): { account_id: number, units: number, reason: string } {
  return { account_id: accountId, units, reason: reason.trim() }
}
