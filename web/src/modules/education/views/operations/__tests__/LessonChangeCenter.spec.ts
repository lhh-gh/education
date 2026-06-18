import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { conflictErrorText, operationPermissions } from '../operationRules.ts'

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

describe('lesson change center', () => {
  it('registers_lesson_change_route', () => {
    const route = findRoute('EducationOperationLessonChangeCenter')

    expect(route.path).toBe('/education/operations/lesson-changes')
    expect(route.meta?.auth).toEqual(['education:operations:lesson-change:page'])
  })

  it('conflict_response_opens_error_block', () => {
    expect(conflictErrorText({ code: 409, message: 'classroom time conflict' })).toBe('classroom time conflict')
  })

  it('batch_button_follows_permission', () => {
    const permissions = operationPermissions(code => code === 'education:operations:lesson-change:batch')

    expect(permissions.batchLessonChange).toBe(true)
    expect(permissions.approveConsumption).toBe(false)
  })
})
