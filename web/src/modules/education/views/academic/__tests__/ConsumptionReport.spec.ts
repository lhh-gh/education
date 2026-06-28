import { describe, expect, it } from 'vitest'
import { reportTagType, summaryMetricItems } from '../reportRules.ts'

describe('consumption report page', () => {
  it('renders_consumption_summary_and_rows', () => {
    expect(summaryMetricItems({ net_units: '106.00' }, ['net_units'])).toEqual([{ title: '净课消', value: '106.00' }])
  })

  it('reversed_rows_use_neutral_badge', () => {
    expect(reportTagType('reversed')).toBe('info')
  })
})
