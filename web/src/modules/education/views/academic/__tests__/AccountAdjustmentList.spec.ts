import { describe, expect, it } from 'vitest'
import {
  buildSupplementDeductionPayload,
  canRollbackAdjustment,
  markAdjustmentRollbackSuccess,
} from '../attendanceConsumptionRules.ts'

describe('account adjustment list', () => {
  it('creates_supplement_deduction', () => {
    expect(buildSupplementDeductionPayload(10, 2, ' material fee ')).toEqual({
      account_id: 10,
      units: 2,
      reason: 'material fee',
    })
  })

  it('adjustment_rollback_success_marks_original_rolled_back', () => {
    const rows = markAdjustmentRollbackSuccess([
      {
        id: 1,
        adjustment_no: 'ADJ001',
        account_id: 10,
        student_id: 20,
        course_id: 30,
        adjustment_type: 'supplement_deduction',
        direction: 'decrease',
        units: '1.00',
        before_available_units: '10.00',
        after_available_units: '9.00',
        status: 'confirmed',
        reason: 'material fee',
      },
    ], {
      id: 1,
      adjustment_no: 'ADJ001',
      account_id: 10,
      student_id: 20,
      course_id: 30,
      adjustment_type: 'supplement_deduction',
      direction: 'decrease',
      units: '1.00',
      before_available_units: '10.00',
      after_available_units: '9.00',
      status: 'rolled_back',
      reason: 'material fee',
    })

    expect(rows[0].status).toBe('rolled_back')
  })

  it('insufficient_balance_keeps_adjustment_form_open', () => {
    expect(canRollbackAdjustment({
      id: 1,
      adjustment_no: 'ADJ001',
      account_id: 10,
      student_id: 20,
      course_id: 30,
      adjustment_type: 'supplement_deduction',
      direction: 'decrease',
      units: '1.00',
      before_available_units: '10.00',
      after_available_units: '9.00',
      status: 'rolled_back',
      reason: 'material fee',
    }, true)).toBe(false)
  })
})
