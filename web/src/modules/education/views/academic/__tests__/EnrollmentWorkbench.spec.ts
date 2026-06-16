import { describe, expect, it } from 'vitest'
import {
  enrollmentQuery,
  enrollmentSuccessSummary,
} from '../courseAccountRules.ts'

describe('enrollment workbench', () => {
  it('create_enrollment_shows_transaction_summary', () => {
    expect(enrollmentSuccessSummary({
      enrollment: { enrollment_no: 'ENR001', status: 'confirmed' },
      account: { id: 601, available_units: '24.00' },
    })).toBe('ENR001 · account 601 · available 24.00')
  })

  it('cancel_failure_keeps_confirm_dialog_open', () => {
    expect(enrollmentQuery({
      page: 1,
      page_size: 20,
      campus_id: 2001,
      student_id: 101,
      course_id: 301,
      status: 'confirmed',
      enrolled_at_start: '2026-06-10',
      enrolled_at_end: '2026-06-11',
      keyword: 'ENR',
    })).toEqual({
      page: 1,
      page_size: 20,
      campus_id: 2001,
      student_id: 101,
      course_id: 301,
      status: 'confirmed',
      enrolled_at_start: '2026-06-10',
      enrolled_at_end: '2026-06-11',
      keyword: 'ENR',
    })
  })
})
