import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import {
  activeContractRiskWarning,
  dataScopePreviewText,
  groupBusinessTypeLabel,
  groupRiskLabel,
  groupStatusLabel,
  metricCards,
} from '../groupRules.ts'

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

describe('group MineAdmin alignment', () => {
  it('uses_chinese_route_titles_for_group_menu', () => {
    expect(findRoute('EducationGroup').meta?.title).toBe('集团管控')

    const expectedRoutes = [
      ['EducationGroupOperationDashboard', '集团看板'],
      ['EducationGroupOrgUnitTree', '组织架构'],
      ['EducationGroupDataPermissionList', '数据权限'],
      ['EducationGroupApprovalTemplateList', '审批模板'],
      ['EducationGroupApprovalTaskList', '审批任务'],
      ['EducationGroupContractList', '合同管理'],
      ['EducationGroupContractRenewalList', '合同续签'],
      ['EducationGroupFranchiseRecordList', '加盟管理'],
      ['EducationGroupRiskAuditEventList', '风控审计'],
    ] as const

    for (const [name, title] of expectedRoutes) {
      expect(findRoute(name).meta?.title).toBe(title)
    }
  })

  it('removes_legacy_english_copy_from_group_vue_pages', () => {
    const groupViewDir = resolve(process.cwd(), 'src/modules/education/views/group')
    const vueFiles = [
      'ApprovalTaskList.vue',
      'ApprovalTemplateList.vue',
      'ContractList.vue',
      'ContractRenewalList.vue',
      'DataPermissionList.vue',
      'FranchiseRecordList.vue',
      'GroupOperationDashboard.vue',
      'OrgUnitTree.vue',
      'RiskAuditEventList.vue',
      'components/ApprovalTemplateEditor.vue',
      'components/ContractForm.vue',
      'components/ContractRenewalDrawer.vue',
      'components/DataPermissionForm.vue',
      'components/OrgUnitForm.vue',
    ]
    const legacyCopies = [
      '<span>Group Dashboard</span>',
      '<span>Org Units</span>',
      '<span>Data Permissions</span>',
      '<span>Approval Templates</span>',
      '<span>Approval Tasks</span>',
      '<span>Contracts</span>',
      '<span>Contract Renewals</span>',
      '<span>Franchise Records</span>',
      '<span>Risk Audit Events</span>',
      'New Contract',
      'New Template',
      'New Record',
      'New Org',
      '>Refresh<',
      '>Search<',
      '>Submit<',
      '>Complete<',
      '>Cancel<',
      '>Save<',
      'No contracts',
      'No approval',
      'No data permissions',
      'No risk events',
    ]

    for (const file of vueFiles) {
      const content = readFileSync(resolve(groupViewDir, file), 'utf8')

      for (const copy of legacyCopies) {
        expect(content, `${file} still contains [${copy}]`).not.toContain(copy)
      }
    }
  })

  it('maps_group_status_risk_business_and_metrics_to_chinese', () => {
    expect(groupStatusLabel('pending')).toBe('待处理')
    expect(groupStatusLabel('completed')).toBe('已完成')
    expect(groupStatusLabel('active')).toBe('生效中')
    expect(groupRiskLabel('critical')).toBe('严重')
    expect(groupRiskLabel('normal')).toBe('正常')
    expect(groupBusinessTypeLabel('contract')).toBe('合同')
    expect(dataScopePreviewText({ allowed_campus_ids: [] })).toBe('暂无校区范围')
    expect(activeContractRiskWarning({ status: 'active', amount_cents: 1000, risk_level: 'high' }, { amount_cents: 1200 })).toBe('生效合同金额发生变化')
    expect(metricCards({ campus_count: 2, student_count: 30, revenue_cents: 1200, renewal_alert_count: 1 }).map(item => item.title)).toEqual(['校区数', '学员数', '营收金额', '续签提醒'])
  })
})
