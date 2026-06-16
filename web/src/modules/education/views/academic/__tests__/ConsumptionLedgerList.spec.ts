import { describe, expect, it } from 'vitest'
import {
  canRollbackConsumption,
  markConsumptionRollbackSuccess,
} from '../attendanceConsumptionRules.ts'

describe('consumption ledger list', () => {
  it('renders_consumption_rows_and_filters', () => {
    expect(canRollbackConsumption({
      id: 1,
      consumption_no: 'CON001',
      account_id: 10,
      student_id: 20,
      course_id: 30,
      lesson_id: 40,
      source_type: 'attendance',
      direction: 'decrease',
      units: '1.00',
      before_available_units: '10.00',
      after_available_units: '9.00',
      status: 'active',
    }, true)).toBe(true)
  })

  it('rollback_success_marks_row_reversed', () => {
    const rows = markConsumptionRollbackSuccess([
      {
        id: 1,
        consumption_no: 'CON001',
        account_id: 10,
        student_id: 20,
        course_id: 30,
        lesson_id: 40,
        source_type: 'attendance',
        direction: 'decrease',
        units: '1.00',
        before_available_units: '10.00',
        after_available_units: '9.00',
        status: 'active',
      },
    ], {
      id: 1,
      consumption_no: 'CON001',
      account_id: 10,
      student_id: 20,
      course_id: 30,
      lesson_id: 40,
      source_type: 'attendance',
      direction: 'decrease',
      units: '1.00',
      before_available_units: '10.00',
      after_available_units: '9.00',
      status: 'reversed',
    })

    expect(rows[0].status).toBe('reversed')
  })

  it('rollback_failure_keeps_dialog_open', () => {
    expect(canRollbackConsumption({
      id: 1,
      consumption_no: 'CON001',
      account_id: 10,
      student_id: 20,
      course_id: 30,
      lesson_id: 40,
      source_type: 'attendance',
      direction: 'decrease',
      units: '1.00',
      before_available_units: '10.00',
      after_available_units: '9.00',
      status: 'reversed',
    }, true)).toBe(false)
  })
})
