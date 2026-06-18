import { describe, expect, it } from 'vitest'
import { isForbiddenWorkflowAction, serializeRuleRows } from '../workflowRules.ts'

describe('workflow rule list', () => {
  it('blocks_forbidden_action_types_and_serializes_rows', () => {
    expect(isForbiddenWorkflowAction('modify_finance')).toBe(true)
    expect(isForbiddenWorkflowAction('create_task')).toBe(false)
    expect(serializeRuleRows([{ condition_field: 'alert_level', operator: 'eq', value: 'urgent' }], [{ action_type: 'create_task', config: { task_type: 'renewal_follow' } }])).toEqual({
      conditions: [{ condition_field: 'alert_level', operator: 'eq', condition_value_json: 'urgent' }],
      actions: [{ action_type: 'create_task', action_config_json: { task_type: 'renewal_follow' } }],
    })
  })
})
