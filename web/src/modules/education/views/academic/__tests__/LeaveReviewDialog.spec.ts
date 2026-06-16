import type { LeaveRequestRecord } from '../../../api/academic/lessonChange.ts'
import { describe, expect, it } from 'vitest'
import { applyLeaveReviewSuccess, reviewDialogErrorState } from '../leaveMakeupRescheduleRules.ts'

const row: LeaveRequestRecord = {
  id: 801,
  tenant_id: 1001,
  campus_id: 2001,
  leave_no: 'LEA001',
  source: 'staff',
  leave_type: 'sick',
  lesson_id: 9001,
  lesson_student_id: 10001,
  class_id: 3001,
  course_id: 4001,
  student_id: 5001,
  account_id: 6001,
  reason: 'Sick',
  status: 'pending',
  makeup_required: true,
}

describe('leave review dialog', () => {
  it('approve_success_updates_row_status', () => {
    expect(applyLeaveReviewSuccess(row, 'approved', 'ok')).toMatchObject({
      id: 801,
      status: 'approved',
      review_remark: 'ok',
    })
  })

  it('consumption_failure_keeps_dialog_open', () => {
    expect(reviewDialogErrorState('lesson student already has active consumption')).toEqual({
      visible: true,
      errorText: 'lesson student already has active consumption',
    })
  })
})
