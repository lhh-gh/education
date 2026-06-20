import { describe, expect, it } from 'vitest'
import { alertConvertState } from '../workflowRules.ts'

describe('operation alert list', () => {
  it('handles_duplicate_convert_state', () => {
    expect(alertConvertState({ status: 'converted', converted_task_id: 12 })).toEqual({ disabled: true, label: '已转任务' })
    expect(alertConvertState({ status: 'open' })).toEqual({ disabled: false, label: '转为任务' })
  })
})
