import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'

function flattenRoutes(routes: RouteRecordRaw[]): RouteRecordRaw[] {
  return routes.flatMap(route => [
    route,
    ...flattenRoutes(route.children ?? []),
  ])
}

function findRoute(name: string): RouteRecordRaw {
  const route = flattenRoutes(educationRoutes).find(item => item.name === name)
  if (!route) {
    throw new Error(`route ${name} not found`)
  }

  return route
}

describe('education group routes', () => {
  it('registers_group_route_tree', () => {
    expect(findRoute('EducationGroup')).toMatchObject({
      path: '/education/group',
      redirect: '/education/group/dashboard',
    })

    const expectedRoutes = [
      ['EducationGroupOrgUnitTree', '/education/group/org-units', ['education:group:org:tree']],
      ['EducationGroupDataPermissionList', '/education/group/data-permissions', ['education:group:data-permission:page']],
      ['EducationGroupApprovalTemplateList', '/education/group/approval-templates', ['education:group:approval-template:page']],
      ['EducationGroupApprovalTaskList', '/education/group/approval-tasks', ['education:group:approval-task:page']],
      ['EducationGroupContractList', '/education/group/contracts', ['education:group:contract:page']],
      ['EducationGroupContractRenewalList', '/education/group/contract-renewals', ['education:group:contract-renewal:page']],
      ['EducationGroupOperationDashboard', '/education/group/dashboard', ['education:group:metric:page']],
      ['EducationGroupFranchiseRecordList', '/education/group/franchises', ['education:group:franchise:page']],
      ['EducationGroupRiskAuditEventList', '/education/group/risk-audits', ['education:group:risk-audit:page']],
    ] as const

    for (const [name, path, auth] of expectedRoutes) {
      const route = findRoute(name)

      expect(route.path).toBe(path)
      expect(route.component).toEqual(expect.any(Function))
      expect(route.meta?.auth).toEqual(auth)
    }
  })
})
