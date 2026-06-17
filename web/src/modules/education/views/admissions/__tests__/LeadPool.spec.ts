import type { RouteRecordRaw } from 'vue-router'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { createLead, pageLeads } from '../../../api/admissions/lead.ts'
import educationRoutes from '@/router/modules/education.ts'
import { admissionErrorText, admissionPermissions } from '../admissionRules.ts'

function flattenRoutes(routes: RouteRecordRaw[]): RouteRecordRaw[] {
  return routes.flatMap(route => [route, ...flattenRoutes(route.children ?? [])])
}

function route(name: string): RouteRecordRaw {
  const item = flattenRoutes(educationRoutes).find(route => route.name === name)
  if (!item) {
    throw new Error(`${name} missing`)
  }

  return item
}

function stubHttp() {
  const calls: Array<{ method: string, url: string, data?: unknown, config?: any }> = []
  vi.stubGlobal('useHttp', () => ({
    get: (url: string, config?: any) => {
      calls.push({ method: 'GET', url, config })
      return Promise.resolve({ code: 200, data: { list: [], total: 0 } })
    },
    post: (url: string, data?: unknown, config?: any) => {
      calls.push({ method: 'POST', url, data, config })
      return Promise.resolve({ code: 200, data: {} })
    },
  }))

  return calls
}

afterEach(() => vi.unstubAllGlobals())

describe('lead pool', () => {
  it('registers_lead_pool_route', () => {
    expect(route('EducationAdmissionLeadPool').path).toBe('/education/admissions/leads')
    expect(route('EducationAdmissionLeadPool').meta?.auth).toEqual(['education:admissions:lead:page'])
  })

  it('lead_api_uses_expected_endpoints_and_headers', () => {
    const calls = stubHttp()
    pageLeads({ tenant_id: 1, campus_id: 9, page: 1, pageSize: 20 })
    createLead({ tenant_id: 1, campus_id: 9, contact_name: 'Ms Wang', contact_mobile: '13800000000' })

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/admissions/leads/page'],
      ['POST', '/admin/education/admissions/leads'],
    ])
    expect(calls[0].config.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
  })

  it('assignment_button_follows_permission_and_duplicate_message_is_readable', () => {
    const permissions = admissionPermissions(code => code === 'education:admissions:lead:assign')
    expect(permissions.assignLead).toBe(true)
    expect(permissions.createLead).toBe(false)
    expect(admissionErrorText({ code: 409, data: { lead_id: 99 } })).toBe('Duplicate lead #99')
  })
})
