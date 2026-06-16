import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { canApproveLeave, canCancelLeave, canRejectLeave } from '../leaveMakeupRescheduleRules.ts'

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

describe('leave request list', () => {
  it('renders_leave_table_and_filters', () => {
    const route = findRoute('EducationAcademicLeaveRequestList')

    expect(route.path).toBe('/education/academic/leave-requests')
    expect(route.component).toEqual(expect.any(Function))
    expect(route.meta?.auth).toEqual(['education:academic:leave-request:page'])
  })

  it('permission_buttons_are_hidden_without_permission', () => {
    expect(canApproveLeave('pending', true)).toBe(true)
    expect(canApproveLeave('pending', false)).toBe(false)
    expect(canRejectLeave('approved', true)).toBe(false)
    expect(canCancelLeave('approved', true)).toBe(true)
    expect(canCancelLeave('makeup_scheduled', true)).toBe(false)
  })
})
