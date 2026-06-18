import { describe, expect, it } from 'vitest'
import { channelRoiFilterPayload, roiTagType } from '../growthRules.ts'

describe('channel roi dashboard', () => {
  it('sends_date_source_and_campus_filters', () => {
    expect(channelRoiFilterPayload({ tenant_id: 1, campus_id: 2, source_id: 10, dateRange: ['2026-06-01', '2026-06-10'] })).toEqual({
      tenant_id: 1,
      campus_id: 2,
      source_id: 10,
      start_date: '2026-06-01',
      end_date: '2026-06-10',
    })
  })

  it('labels_roi_quality', () => {
    expect(roiTagType('5.0000')).toBe('success')
    expect(roiTagType('1.0000')).toBe('warning')
    expect(roiTagType(null)).toBe('info')
  })
})
