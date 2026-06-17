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

describe('education family routes', () => {
  it('registers_family_route_tree', () => {
    expect(findRoute('EducationFamily')).toMatchObject({
      path: '/education/family',
      redirect: '/education/family/homework',
    })

    const expectedRoutes = [
      ['EducationFamilyCommentTemplateList', '/education/family/comment-templates', ['education:family:comment-template:page']],
      ['EducationFamilyPerformanceTagList', '/education/family/performance-tags', ['education:family:performance-tag:page']],
      ['EducationFamilyHomeworkAssignmentList', '/education/family/homework', ['education:family:homework:page']],
      ['EducationFamilyLearningReportList', '/education/family/reports', ['education:family:report:page']],
      ['EducationFamilyGrowthRecordList', '/education/family/growth-records', ['education:family:growth:page']],
      ['EducationFamilyMessageMonitor', '/education/family/messages', ['education:family:message:page']],
      ['EducationFamilyServiceQualityDashboard', '/education/family/quality', ['education:family:quality:page']],
    ] as const

    for (const [name, path, auth] of expectedRoutes) {
      const route = findRoute(name)

      expect(route.path).toBe(path)
      expect(route.component).toEqual(expect.any(Function))
      expect(route.meta?.auth).toEqual(auth)
    }
  })
})
