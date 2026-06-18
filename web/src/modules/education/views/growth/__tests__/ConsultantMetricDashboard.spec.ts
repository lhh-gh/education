import { describe, expect, it } from 'vitest'
import { consultantMetricFilterPayload, consultantConversionRate } from '../growthRules.ts'

describe('consultant metric dashboard', () => {
  it('sends_consultant_and_date_filters', () => {
    expect(consultantMetricFilterPayload({ tenant_id: 1, campus_id: 2, consultant_user_id: 8, dateRange: ['2026-06-01', '2026-06-10'] })).toEqual({
      tenant_id: 1,
      campus_id: 2,
      consultant_user_id: 8,
      start_date: '2026-06-01',
      end_date: '2026-06-10',
    })
  })

  it('calculates_conversion_rate', () => {
    expect(consultantConversionRate({ assigned_leads_count: 10, converted_count: 3 })).toBe('30.00%')
    expect(consultantConversionRate({ assigned_leads_count: 0, converted_count: 0 })).toBe('0.00%')
  })
})
