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

describe('education foundation routes', () => {
  it('registers_foundation_route_tree', () => {
    expect(findRoute('EducationRoot')).toMatchObject({
      path: '/education',
      redirect: '/education/foundation/tenants',
      meta: {
        auth: ['education:*'],
      },
    })

    const expectedRoutes = [
      {
        name: 'EducationFoundationTenantList',
        path: '/education/foundation/tenants',
        auth: ['education:foundation:tenant:page'],
      },
      {
        name: 'EducationFoundationCampusList',
        path: '/education/foundation/campuses',
        auth: ['education:foundation:campus:page'],
      },
      {
        name: 'EducationFoundationUserProfileList',
        path: '/education/foundation/user-profiles',
        auth: ['education:foundation:user-profile:page'],
      },
      {
        name: 'EducationFoundationDictionaryList',
        path: '/education/foundation/dictionaries',
        auth: ['education:foundation:dictionary:page'],
      },
      {
        name: 'EducationFoundationFeatureFlagList',
        path: '/education/foundation/feature-flags',
        auth: ['education:foundation:feature-flag:page'],
      },
      {
        name: 'EducationFoundationAuditLogList',
        path: '/education/foundation/audit-logs',
        auth: ['education:foundation:audit-log:page'],
      },
    ]

    for (const expected of expectedRoutes) {
      const route = findRoute(expected.name)

      expect(route.path).toBe(expected.path)
      expect(route.component).toEqual(expect.any(Function))
      expect(route.meta?.auth).toEqual(expected.auth)
    }
  })

  it('registers_v1_profile_record_route_tree', () => {
    const academicRoot = findRoute('EducationAcademic')

    expect(academicRoot).toMatchObject({
      path: '/education/academic',
      redirect: '/education/academic/classrooms',
      meta: {
        auth: [
          'education:academic:classroom:page',
          'education:academic:student:page',
          'education:academic:guardian:page',
          'education:academic:teacher:page',
          'education:academic:course:page',
          'education:academic:lesson-package:page',
          'education:academic:enrollment:page',
          'education:academic:student-course-account:page',
        ],
      },
    })

    const expectedRoutes = [
      {
        name: 'EducationAcademicClassroomList',
        path: '/education/academic/classrooms',
        auth: ['education:academic:classroom:page'],
      },
      {
        name: 'EducationAcademicStudentList',
        path: '/education/academic/students',
        auth: ['education:academic:student:page'],
      },
      {
        name: 'EducationAcademicGuardianList',
        path: '/education/academic/guardians',
        auth: ['education:academic:guardian:page'],
      },
      {
        name: 'EducationAcademicTeacherList',
        path: '/education/academic/teachers',
        auth: ['education:academic:teacher:page'],
      },
      {
        name: 'EducationAcademicCourseList',
        path: '/education/academic/courses',
        auth: ['education:academic:course:page'],
      },
      {
        name: 'EducationAcademicLessonPackageList',
        path: '/education/academic/lesson-packages',
        auth: ['education:academic:lesson-package:page'],
      },
      {
        name: 'EducationAcademicEnrollmentWorkbench',
        path: '/education/academic/enrollments',
        auth: ['education:academic:enrollment:page'],
      },
      {
        name: 'EducationAcademicAccountLedgerList',
        path: '/education/academic/course-accounts',
        auth: ['education:academic:student-course-account:page'],
      },
    ]

    for (const expected of expectedRoutes) {
      const route = findRoute(expected.name)

      expect(route.path).toBe(expected.path)
      expect(route.component).toEqual(expect.any(Function))
      expect(route.meta?.auth).toEqual(expected.auth)
    }
  })

  it('has_no_duplicate_route_names', () => {
    const names = flattenRoutes(educationRoutes)
      .map(route => route.name)
      .filter(Boolean)

    expect(new Set(names).size).toBe(names.length)
  })
})
