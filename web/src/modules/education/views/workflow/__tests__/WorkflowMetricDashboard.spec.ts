import { describe, expect, it } from 'vitest'
import { metricFilterPayload } from '../workflowRules.ts'

describe('workflow metric dashboard', () => {
  it('sends_date_campus_and_task_filters', () => {
    expect(metricFilterPayload({ tenant_id: 1, campus_id: 2, dateRange: ['2026-06-01', '2026-06-10'], task_type: 'renewal_follow' })).toEqual({
      tenant_id: 1,
      campus_id: 2,
      start_date: '2026-06-01',
      end_date: '2026-06-10',
      task_type: 'renewal_follow',
    })
  })
})
