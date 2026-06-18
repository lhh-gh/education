import type { RenewalAlertRecord } from '../../../api/operations/renewal.ts'
import { describe, expect, it } from 'vitest'
import { sortRenewalAlerts } from '../operationRules.ts'

function alert(id: number, level: RenewalAlertRecord['alert_level']): RenewalAlertRecord {
  return { id, tenant_id: 1, campus_id: 1, student_id: id, course_id: 1, student_course_account_id: 1, alert_type: 'low_balance', alert_level: level, status: 'open', due_date: '2026-06-20' }
}

describe('renewal alert list', () => {
  it('urgent_alerts_sort_before_normal_alerts_on_same_due_date', () => {
    expect(sortRenewalAlerts([alert(1, 'normal'), alert(2, 'urgent'), alert(3, 'warning')]).map(item => item.id)).toEqual([2, 3, 1])
  })
})
