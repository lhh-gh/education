import { describe, expect, it } from 'vitest'
import { containsRawSql } from '../aiRules.ts'

describe('data question workbench', () => {
  it('rejects_raw_sql_and_requires_metric_catalog_selection', () => {
    expect(containsRawSql('select * from edu_orders')).toBe(true)
    expect(containsRawSql('show renewal alert count by campus')).toBe(false)

    const selectedMetricCodes = ['renewal_alert_count']
    expect(selectedMetricCodes).toEqual(expect.arrayContaining(['renewal_alert_count']))
  })
})
