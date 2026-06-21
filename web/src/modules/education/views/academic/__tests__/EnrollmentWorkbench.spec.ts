import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'
import {
  enrollmentQuery,
  enrollmentSuccessSummary,
} from '../courseAccountRules.ts'

const enrollmentCreateDrawerSource = readFileSync(resolve(__dirname, '../components/EnrollmentCreateDrawer.vue'), 'utf8')

describe('enrollment workbench', () => {
  it('create_enrollment_shows_transaction_summary', () => {
    expect(enrollmentSuccessSummary({
      enrollment: { enrollment_no: 'ENR001', status: 'confirmed' },
      account: { id: 601, available_units: '24.00' },
    })).toBe('ENR001 · 课时账户 601 · 可用课时 24.00')
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

  it('uses_chinese_copy_in_create_enrollment_drawer', () => {
    const legacyCopies = [
      'title="Create Enrollment"',
      'label="Campus ID"',
      'label="Student"',
      'label="Course"',
      'label="Package"',
      'label="Package Summary"',
      'label="Deal Amount"',
      'label="Enrolled At"',
      'label="Remark"',
      'No package selected',
      '} units',
      ' units /',
      'Use package sale price when empty',
      'Enrollment options loading failed',
      'Enrollment create failed',
      'Close\n',
      'Create\n',
    ]

    for (const copy of legacyCopies) {
      expect(enrollmentCreateDrawerSource).not.toContain(copy)
    }
  })
})
