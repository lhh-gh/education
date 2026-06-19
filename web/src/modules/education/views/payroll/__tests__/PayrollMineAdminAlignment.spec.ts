import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { payrollConflictText, payrollStatusLabel, payrollTypeLabel } from '../payrollRules.ts'

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

function expectMaterialSymbolsIconExists(icon: unknown): void {
  expect(icon).toEqual(expect.any(String))

  const [collection, name] = String(icon).split(':')
  expect(collection).toBe('material-symbols')

  const iconJson = readFileSync(
    resolve(process.cwd(), `node_modules/@iconify/json/json/${collection}.json`),
    'utf8',
  )

  expect(iconJson).toContain(`"${name}"`)
}

describe('payroll MineAdmin alignment', () => {
  it('uses_chinese_route_titles_and_supported_icon_for_payroll_menu', () => {
    expect(findRoute('EducationPayroll').meta?.title).toBe('薪酬绩效')
    expect(findRoute('EducationPayroll').meta?.icon).toBe('material-symbols:price-check-rounded')
    expectMaterialSymbolsIconExists(findRoute('EducationPayroll').meta?.icon)

    const expectedRoutes = [
      ['EducationPayrollSalaryRuleList', '薪酬规则'],
      ['EducationPayrollSalaryBatchList', '薪酬批次'],
      ['EducationPayrollSalarySlipList', '工资条'],
      ['EducationPayrollSalaryReviewList', '薪酬复核'],
      ['EducationPayrollSalaryPaymentList', '薪酬发放'],
      ['EducationPayrollWorkloadDisputeList', '工作量申诉'],
      ['EducationPayrollTeacherPerformanceDashboard', '教师绩效'],
    ] as const

    for (const [name, title] of expectedRoutes) {
      expect(findRoute(name).meta?.title).toBe(title)
    }
  })

  it('removes_legacy_english_copy_from_payroll_vue_pages', () => {
    const payrollViewDir = resolve(process.cwd(), 'src/modules/education/views/payroll')
    const vueFiles = [
      'SalaryRuleList.vue',
      'SalaryBatchList.vue',
      'SalarySlipList.vue',
      'SalaryReviewList.vue',
      'SalaryPaymentList.vue',
      'WorkloadDisputeList.vue',
      'TeacherPerformanceDashboard.vue',
      'components/SalaryRuleForm.vue',
      'components/SalaryBatchCalculateForm.vue',
      'components/SalarySlipDetailDrawer.vue',
      'components/SalaryAdjustmentForm.vue',
      'components/WorkloadDisputeReviewDrawer.vue',
    ]
    const legacyCopies = [
      'Teacher Payroll',
      'Salary Rules',
      'Salary Batches',
      'Salary Slips',
      'Salary Reviews',
      'Salary Payments',
      'Workload Disputes',
      'Teacher Performance',
      'New Rule',
      'Calculate Salary Batch',
      'Salary Slip Detail',
      'Salary Adjustment',
      'Review Dispute',
      'Mark Salary Paid',
      'Search',
      'Refresh',
      'Actions',
      'Cancel',
      'Submit',
      'Save',
      'No salary',
    ]

    for (const file of vueFiles) {
      const content = readFileSync(resolve(payrollViewDir, file), 'utf8')

      for (const copy of legacyCopies) {
        expect(content, `${file} still contains [${copy}]`).not.toContain(copy)
      }
    }
  })

  it('maps_payroll_status_types_and_errors_to_chinese', () => {
    expect(payrollStatusLabel('enabled')).toBe('启用')
    expect(payrollStatusLabel('submitted')).toBe('已提交')
    expect(payrollStatusLabel('paid')).toBe('已发放')
    expect(payrollTypeLabel('lesson')).toBe('课次')
    expect(payrollTypeLabel('performance')).toBe('绩效')
    expect(payrollConflictText('salary batch is not submitted')).toBe('薪酬批次未提交')
    expect(payrollConflictText()).toBe('')
  })
})
