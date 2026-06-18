import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { canSubmitAttendance } from '../attendanceConsumptionRules.ts'

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

describe('attendance review', () => {
  it('renders_attendance_lesson_table_and_filters', () => {
    const route = findRoute('EducationAcademicAttendanceReview')

    expect(route.path).toBe('/education/academic/attendance-review')
    expect(route.component).toEqual(expect.any(Function))
    expect(route.meta?.auth).toEqual(['education:academic:attendance:lesson-page'])
  })

  it('permission_buttons_are_hidden_without_permission', () => {
    expect(canSubmitAttendance('scheduled', true)).toBe(true)
    expect(canSubmitAttendance('scheduled', false)).toBe(false)
    expect(canSubmitAttendance('completed', true)).toBe(false)
  })
})
