import { describe, expect, it } from 'vitest'
import { qualityFilterPayload } from '../standardRules.ts'

describe('course quality dashboard', () => {
  it('passes_course_date_campus_filters_to_quality_api', () => {
    expect(qualityFilterPayload({ tenant_id: 1, campus_id: 2, course_id: 3, start_date: '2026-06-01', end_date: '2026-06-10' })).toEqual({
      tenant_id: 1,
      campus_id: 2,
      course_id: 3,
      start_date: '2026-06-01',
      end_date: '2026-06-10',
    })
  })
})
