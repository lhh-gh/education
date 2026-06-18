import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { canRescheduleLesson, detailDrawerTitle, lessonChangeTypeLabel } from '../leaveMakeupRescheduleRules.ts'

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

describe('lesson change list', () => {
  it('renders_lesson_change_rows', () => {
    const route = findRoute('EducationAcademicLessonChangeList')

    expect(route.path).toBe('/education/academic/lesson-changes')
    expect(route.meta?.auth).toEqual(['education:academic:lesson-change:page'])
    expect(lessonChangeTypeLabel('makeup')).toBe('Make-up')
    expect(detailDrawerTitle({ id: 1, tenant_id: 1, campus_id: 1, change_no: 'CHG001', change_type: 'reschedule', status: 'confirmed', source_lesson_id: 11, class_id: 21, course_id: 31, lesson_units: '1.00', reason: 'move' })).toBe('Reschedule CHG001')
  })

  it('action_buttons_follow_permissions', () => {
    expect(canRescheduleLesson(true)).toBe(true)
    expect(canRescheduleLesson(false)).toBe(false)
  })
})
