import { afterEach, describe, expect, it, vi } from 'vitest'
import { convertLead } from '../../../api/admissions/lead.ts'

afterEach(() => vi.unstubAllGlobals())

describe('lead conversion workbench', () => {
  it('conversion_success_links_student_and_enrollment_ids', async () => {
    vi.stubGlobal('useHttp', () => ({
      post: (url: string, data?: unknown, config?: any) => {
        expect(url).toBe('/admin/education/admissions/leads/101/convert')
        expect(config.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
        return Promise.resolve({ code: 200, data: { student_id: 1201, enrollment_id: 1401 } })
      },
    }))

    const response = await convertLead(101, { tenant_id: 1, campus_id: 9, student_name: 'Kid', guardian_name: 'Parent', lesson_package_id: 9001 })

    expect(response.data).toMatchObject({ student_id: 1201, enrollment_id: 1401 })
  })
})
