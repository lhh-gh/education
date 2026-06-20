import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { alertConvertState, workflowStatusLabel } from '../workflowRules.ts'

function flattenRoutes(routes: RouteRecordRaw[]): RouteRecordRaw[] {
  return routes.flatMap(route => [route, ...flattenRoutes(route.children ?? [])])
}

function findRoute(name: string): RouteRecordRaw {
  const route = flattenRoutes(educationRoutes).find(item => item.name === name)
  if (!route) {
    throw new Error(`route ${name} not found`)
  }

  return route
}

describe('workflow MineAdmin alignment', () => {
  it('uses_chinese_route_titles_for_workflow_menu', () => {
    expect(findRoute('EducationWorkflow').meta?.title).toBe('工作流中心')

    const expectedRoutes = [
      ['EducationWorkflowRuleList', '自动化规则'],
      ['EducationWorkflowTaskWorkbench', '待办任务'],
      ['EducationWorkflowOperationAlertList', '运营告警'],
      ['EducationWorkflowSlaPolicyList', 'SLA 策略'],
      ['EducationWorkflowEscalationPolicyList', '升级策略'],
      ['EducationWorkflowTemplateList', '流程模板'],
      ['EducationWorkflowMetricDashboard', '工作流看板'],
    ] as const

    for (const [name, title] of expectedRoutes) {
      expect(findRoute(name).meta?.title).toBe(title)
    }
  })

  it('removes_legacy_english_copy_from_workflow_vue_pages', () => {
    const workflowViewDir = resolve(process.cwd(), 'src/modules/education/views/workflow')
    const vueFiles = [
      'WorkflowRuleList.vue',
      'WorkflowTaskWorkbench.vue',
      'OperationAlertList.vue',
      'SlaPolicyList.vue',
      'EscalationPolicyList.vue',
      'WorkflowTemplateList.vue',
      'WorkflowMetricDashboard.vue',
      'components/TaskCommentPanel.vue',
      'components/WorkflowTaskDetailDrawer.vue',
      'components/WorkflowRuleEditor.vue',
    ]
    const legacyCopies = [
      'Workflow Rules',
      'Task Workbench',
      'Operation Alerts',
      'SLA Policies',
      'Escalation Policies',
      'Workflow Templates',
      'Workflow Metrics',
      '>Save<',
      '>Complete<',
      '>Refresh<',
      'No workflow tasks',
      'No workflow rules',
      'No alerts',
      'Task Detail',
      'Rule Code',
      'Rule Name',
      'Event Type',
      'placeholder="Comment"',
      'label="Title"',
      'label="Status"',
      'label="Actions"',
      'label="Task Type"',
      'label="Due Minutes"',
      'label="Overdue Minutes"',
    ]

    for (const file of vueFiles) {
      const content = readFileSync(resolve(workflowViewDir, file), 'utf8')

      for (const copy of legacyCopies) {
        expect(content, `${file} still contains [${copy}]`).not.toContain(copy)
      }
    }
  })

  it('maps_workflow_states_and_actions_to_chinese', () => {
    expect(alertConvertState({ status: 'converted', converted_task_id: 12 })).toEqual({ disabled: true, label: '已转任务' })
    expect(alertConvertState({ status: 'open' })).toEqual({ disabled: false, label: '转为任务' })
    expect(workflowStatusLabel('pending')).toBe('待处理')
    expect(workflowStatusLabel('processing')).toBe('处理中')
    expect(workflowStatusLabel('completed')).toBe('已完成')
    expect(workflowStatusLabel('overdue')).toBe('已逾期')
  })
})
