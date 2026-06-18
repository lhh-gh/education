import { describe, expect, it } from 'vitest'
import { qualityFilterParams } from '../familyRules.ts'

describe('service quality dashboard', () => {
  it('asserts_date_campus_teacher_filters_are_sent_to_quality_apis', () => {
    expect(qualityFilterParams({ campus_id: 3, teacher_id: 8, start_date: '2026-06-01', end_date: '2026-06-30' })).toEqual({
      campus_id: 3,
      teacher_id: 8,
      start_date: '2026-06-01',
      end_date: '2026-06-30',
    })
  })
})
