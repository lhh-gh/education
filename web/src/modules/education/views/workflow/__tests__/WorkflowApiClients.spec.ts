import { afterEach, describe, expect, it, vi } from 'vitest'
import { closeAlert, convertAlertToTask, ignoreAlert, pageOperationAlerts } from '../../../api/workflow/alert.ts'
import { getWorkflowDashboard, getWorkflowMetrics } from '../../../api/workflow/metric.ts'
import { pageEscalationPolicies, pageSlaPolicies, saveEscalationPolicy, saveSlaPolicy } from '../../../api/workflow/policy.ts'
import { enableWorkflowRule, pageWorkflowRules, saveWorkflowRule } from '../../../api/workflow/rule.ts'
import { addWorkflowTaskComment, completeWorkflowTask, pageWorkflowTasks, uploadTaskAttachment } from '../../../api/workflow/task.ts'
import { pageWorkflowTemplates, saveWorkflowTemplate } from '../../../api/workflow/template.ts'

interface HttpCall {
  method: 'GET' | 'POST'
  url: string
  data?: unknown
  config?: any
}

function stubHttp(): HttpCall[] {
  const calls: HttpCall[] = []
  const response = Promise.resolve({ code: 200, message: 'success', data: {} })

  vi.stubGlobal('useHttp', () => ({
    get: (url: string, config?: unknown) => {
      calls.push({ method: 'GET', url, config })
      return response
    },
    post: (url: string, data?: unknown, config?: unknown) => {
      calls.push({ method: 'POST', url, data, config })
      return response
    },
  }))

  return calls
}

afterEach(() => vi.unstubAllGlobals())

describe('education workflow api clients', () => {
  it('uses_expected_workflow_endpoints_and_scope_headers', () => {
    const calls = stubHttp()

    pageWorkflowRules({ tenant_id: 1, campus_id: 9 })
    saveWorkflowRule({ tenant_id: 1, rule_code: 'LOW', rule_name: 'Low', event_type: 'renewal', actions: [{ action_type: 'create_task', action_config_json: { task_type: 'renewal_follow' } }] })
    enableWorkflowRule(3, { tenant_id: 1, enabled: true })
    pageWorkflowTasks({ tenant_id: 1 })
    completeWorkflowTask(4, { tenant_id: 1, result: 'done', content: 'ok' })
    addWorkflowTaskComment(4, { tenant_id: 1, content: 'note' })
    uploadTaskAttachment(4, { tenant_id: 1, file_name: 'a.txt', file_url: '/a.txt' })
    pageOperationAlerts({ tenant_id: 1 })
    convertAlertToTask(5, { tenant_id: 1, assignee_user_id: 8 })
    ignoreAlert(5, { tenant_id: 1 })
    closeAlert(5, { tenant_id: 1 })
    pageSlaPolicies({ tenant_id: 1 })
    saveSlaPolicy({ tenant_id: 1, policy_code: 'P1', policy_name: 'P1', task_type: 'renewal_follow', due_minutes: 60 })
    pageEscalationPolicies({ tenant_id: 1 })
    saveEscalationPolicy({ tenant_id: 1, policy_code: 'E1', task_type: 'renewal_follow', overdue_minutes: 30, escalate_to_user_ids_json: [9] })
    pageWorkflowTemplates({ tenant_id: 1 })
    saveWorkflowTemplate({ tenant_id: 1, template_code: 'T1', template_name: 'T1', task_type: 'renewal_follow', template_json: {} })
    getWorkflowMetrics({ tenant_id: 1 })
    getWorkflowDashboard({ tenant_id: 1 })

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/workflow/rules/page'],
      ['POST', '/admin/education/workflow/rules'],
      ['POST', '/admin/education/workflow/rules/3/enable'],
      ['GET', '/admin/education/workflow/tasks/page'],
      ['POST', '/admin/education/workflow/tasks/4/complete'],
      ['POST', '/admin/education/workflow/tasks/4/comments'],
      ['POST', '/admin/education/workflow/tasks/4/attachments'],
      ['GET', '/admin/education/workflow/alerts/page'],
      ['POST', '/admin/education/workflow/alerts/5/convert-task'],
      ['POST', '/admin/education/workflow/alerts/5/ignore'],
      ['POST', '/admin/education/workflow/alerts/5/close'],
      ['GET', '/admin/education/workflow/sla-policies/page'],
      ['POST', '/admin/education/workflow/sla-policies'],
      ['GET', '/admin/education/workflow/escalation-policies/page'],
      ['POST', '/admin/education/workflow/escalation-policies'],
      ['GET', '/admin/education/workflow/templates/page'],
      ['POST', '/admin/education/workflow/templates'],
      ['GET', '/admin/education/workflow/metrics'],
      ['GET', '/admin/education/workflow/dashboard'],
    ])
    expect(calls[0].config.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
  })
})
