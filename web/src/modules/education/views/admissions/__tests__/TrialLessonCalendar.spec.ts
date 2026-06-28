import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { admissionErrorText } from '../admissionRules.ts'

function flattenRoutes(routes: RouteRecordRaw[]): RouteRecordRaw[] {
  return routes.flatMap(route => [route, ...flattenRoutes(route.children ?? [])])
}

describe('trial lesson calendar', () => {
  it('registers_trial_route_and_conflict_message', () => {
    const route = flattenRoutes(educationRoutes).find(route => route.name === 'EducationAdmissionTrialCalendar')
    expect(route?.path).toBe('/education/admissions/trials')
    expect(route?.meta?.auth).toEqual(['education:admissions:trial:page'])
    expect(admissionErrorText({ code: 409, data: { conflict_lesson_id: 401 } })).toBe('试听时间冲突：#401')
  })
})
