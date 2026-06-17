import { afterEach, describe, expect, it, vi } from 'vitest'
import { getAdmissionOverview } from '../../../api/admissions/dashboard.ts'

afterEach(() => vi.unstubAllGlobals())

describe('admission dashboard', () => {
  it('campus_and_date_filters_are_passed_to_dashboard_api', async () => {
    vi.stubGlobal('useHttp', () => ({
      get: (url: string, config?: any) => {
        expect(url).toBe('/admin/education/admissions/dashboard/overview')
        expect(config.params).toMatchObject({ tenant_id: 1, campus_id: 9, start_date: '2026-06-01', end_date: '2026-06-30' })
        expect(config.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
        return Promise.resolve({ code: 200, data: { new_leads_count: 1 } })
      },
    }))

    const response = await getAdmissionOverview({ tenant_id: 1, campus_id: 9, start_date: '2026-06-01', end_date: '2026-06-30' })
    expect(response.data.new_leads_count).toBe(1)
  })
})
