import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { conflictErrorText, operationDashboardMetricItems, operationStatusLabel } from '../operationRules.ts'

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

describe('operations MineAdmin alignment', () => {
  it('uses_chinese_route_titles_for_operations_menu', () => {
    expect(findRoute('EducationOperations').meta?.title).toBe('运营中心')

    const expectedRoutes = [
      ['EducationOperationLessonChangeCenter', '调课中心'],
      ['EducationOperationMakeupList', '补课闭环'],
      ['EducationOperationConsumptionReviewList', '消课审核'],
      ['EducationOperationRenewalAlertList', '续费提醒'],
      ['EducationOperationTeacherWorkloadReport', '教师工作量'],
      ['EducationOperationDashboard', '运营看板'],
    ] as const

    for (const [name, title] of expectedRoutes) {
      expect(findRoute(name).meta?.title).toBe(title)
    }
  })

  it('removes_legacy_english_copy_from_operations_vue_pages', () => {
    const operationsViewDir = resolve(process.cwd(), 'src/modules/education/views/operations')
    const vueFiles = [
      'ConsumptionReviewList.vue',
      'LeaveMakeupList.vue',
      'LessonChangeCenter.vue',
      'OperationDashboard.vue',
      'RenewalAlertList.vue',
      'TeacherWorkloadReport.vue',
      'components/ConsumptionReviewDrawer.vue',
      'components/LessonChangeForm.vue',
      'components/LessonChangeReviewDrawer.vue',
      'components/MakeupArrangeForm.vue',
      'components/RenewalFollowDrawer.vue',
    ]
    const legacyCopies = [
      'Academic Operations',
      'Lesson Change Center',
      'Leave Make-up Closure',
      'Consumption Review',
      'Renewal Alerts',
      'Teacher Workloads',
      'Operation Dashboard',
      'Lesson Changes',
      'Make-up Open',
      'Pending Reviews',
      'Urgent Renewals',
      'Teacher Credits',
      'Refresh',
      'Search',
      'Actions',
      'Approve',
      'Reject',
      'No records',
      'No dashboard data',
      'Operation failed',
      'Lesson change conflict',
      'Permission denied',
    ]

    for (const file of vueFiles) {
      const content = readFileSync(resolve(operationsViewDir, file), 'utf8')

      for (const copy of legacyCopies) {
        expect(content, `${file} still contains [${copy}]`).not.toContain(copy)
      }
    }
  })

  it('maps_operation_metrics_statuses_and_errors_to_chinese', () => {
    expect(operationDashboardMetricItems({})[0]).toEqual({ title: '调课申请', value: 0 })
    expect(operationStatusLabel('pending')).toBe('待处理')
    expect(operationStatusLabel('approved')).toBe('已通过')
    expect(operationStatusLabel('urgent')).toBe('紧急')
    expect(conflictErrorText({ code: 409 })).toBe('调课时间冲突')
    expect(conflictErrorText({ message: 'Permission denied' })).toBe('暂无操作权限')
    expect(conflictErrorText({})).toBe('运营操作失败')
  })
})
