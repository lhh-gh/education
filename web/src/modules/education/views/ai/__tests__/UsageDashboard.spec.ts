import { afterEach, describe, expect, it, vi } from 'vitest'
import { getUsageSummary } from '../../../api/ai/usage.ts'

afterEach(() => vi.unstubAllGlobals())

describe('usage dashboard', () => {
  it('passes_cost_and_token_filters_to_usage_api', async () => {
    vi.stubGlobal('useHttp', () => ({
      get: (url: string, config?: any) => {
        expect(url).toBe('/admin/education/ai/usage/summary')
        expect(config.params).toMatchObject({ tenant_id: 1, campus_id: 9, start_date: '2026-06-01', end_date: '2026-06-30', feature_code: 'lesson_comment' })
        expect(config.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
        return Promise.resolve({ code: 200, data: { total_tokens: 12000, cost_cents: 300 } })
      },
    }))

    const response = await getUsageSummary({ tenant_id: 1, campus_id: 9, start_date: '2026-06-01', end_date: '2026-06-30', feature_code: 'lesson_comment' })
    expect(response.data.total_tokens).toBe(12000)
    expect(response.data.cost_cents).toBe(300)
  })
})
