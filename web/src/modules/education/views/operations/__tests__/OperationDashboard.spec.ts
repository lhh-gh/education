import { describe, expect, it } from 'vitest'
import { operationRequestOptions } from '../../../api/operations/types.ts'
import { buildDashboardChartParams, operationDashboardMetricItems } from '../operationRules.ts'

describe('operation dashboard', () => {
  it('chart_requests_include_campus_header_and_date_range', () => {
    const params = buildDashboardChartParams({ tenant_id: 1, campus_id: 9, start_at: '2026-06-01 00:00:00', end_at: '2026-06-30 23:59:59' })
    const options = operationRequestOptions(params)

    expect(params).toMatchObject({ start_at: '2026-06-01 00:00:00', end_at: '2026-06-30 23:59:59' })
    expect(options.headers).toMatchObject({ 'X-Tenant-Id': '1', 'X-Campus-Id': '9' })
  })

  it('renders_empty_metric_defaults', () => {
    expect(operationDashboardMetricItems({})[0]).toEqual({ title: '调课申请', value: 0 })
  })
})
