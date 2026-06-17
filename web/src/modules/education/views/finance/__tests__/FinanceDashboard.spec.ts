import { afterEach, describe, expect, it, vi } from 'vitest'
import { getFinanceOverview } from '../../../api/finance/dashboard.ts'

afterEach(() => vi.unstubAllGlobals())

describe('finance dashboard', () => {
  it('campus_and_date_filters_are_passed_to_dashboard_api', async () => {
    vi.stubGlobal('useHttp', () => ({
      get: (url: string, config?: any) => {
        expect(url).toBe('/admin/education/finance/dashboard/summary')
        expect(config.params).toMatchObject({ tenant_id: 1, campus_id: 9, start_at: '2026-06-01 00:00:00', end_at: '2026-06-30 23:59:59' })
        expect(config.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
        return Promise.resolve({ code: 200, data: { order_count: 1 } })
      },
    }))

    const response = await getFinanceOverview({ tenant_id: 1, campus_id: 9, start_at: '2026-06-01 00:00:00', end_at: '2026-06-30 23:59:59' })
    expect(response.data.order_count).toBe(1)
  })
})
