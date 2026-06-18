export const forbiddenWorkflowActions = ['modify_finance', 'modify_payroll', 'modify_enrollment', 'modify_consumption', 'modify_course_account', 'modify_contract', 'modify_attendance']

export function isForbiddenWorkflowAction(actionType: string): boolean {
  return forbiddenWorkflowActions.includes(actionType)
}

export function serializeRuleRows(conditions: Array<{ condition_field: string, operator: string, value: unknown }>, actions: Array<{ action_type: string, config: Record<string, unknown> }>) {
  return {
    conditions: conditions.map(item => ({ condition_field: item.condition_field, operator: item.operator, condition_value_json: item.value })),
    actions: actions.map(item => ({ action_type: item.action_type, action_config_json: item.config })),
  }
}

export function canCompleteTask(task: { assignee_user_ids?: number[], status: string }, currentUserId: number): boolean {
  return task.status !== 'completed' && task.status !== 'cancelled' && (task.assignee_user_ids ?? []).includes(currentUserId)
}

export function isOverdueTask(task: { status: string, due_at?: string }, now = new Date().toISOString()): boolean {
  return task.status === 'overdue' || (!!task.due_at && task.status !== 'completed' && new Date(task.due_at).getTime() < new Date(now).getTime())
}

export function alertConvertState(alert: { status: string, converted_task_id?: number }) {
  return alert.status === 'converted' || !!alert.converted_task_id
    ? { disabled: true, label: 'Converted' }
    : { disabled: false, label: 'Convert' }
}

export function metricFilterPayload(input: { tenant_id?: number, campus_id?: number, dateRange?: [string, string], task_type?: string }) {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    start_date: input.dateRange?.[0],
    end_date: input.dateRange?.[1],
    task_type: input.task_type,
  }
}
