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
  })
})
