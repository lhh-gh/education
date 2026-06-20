import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'

describe('education workflow routes', () => {
  it('registers_workflow_pages', () => {
    const workflow = educationRoutes[0].children?.find(route => route.name === 'EducationWorkflow')
    expect(workflow?.children?.map(route => route.name)).toEqual([
      'EducationWorkflowRuleList',
      'EducationWorkflowTaskWorkbench',
      'EducationWorkflowOperationAlertList',
      'EducationWorkflowSlaPolicyList',
      'EducationWorkflowEscalationPolicyList',
      'EducationWorkflowTemplateList',
      'EducationWorkflowMetricDashboard',
    ])
    expect(workflow?.meta?.title).toBe('工作流中心')
    expect(workflow?.children?.map(route => route.meta?.title)).toEqual([
      '自动化规则',
      '待办任务',
      '运营告警',
      'SLA 策略',
      '升级策略',
      '流程模板',
      '工作流看板',
    ])
  })
})
