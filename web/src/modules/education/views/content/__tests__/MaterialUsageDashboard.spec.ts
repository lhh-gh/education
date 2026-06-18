import { describe, expect, it } from 'vitest'
import { metricFilterPayload } from '../contentRules.ts'

describe('material usage dashboard', () => {
  it('asserts_material_course_date_campus_filters_are_sent', () => {
    expect(metricFilterPayload({
      tenant_id: 1,
      campus_id: 9,
      course_id: 3,
      material_id: 5,
      dateRange: ['2026-06-01', '2026-06-30'],
    })).toEqual({
      tenant_id: 1,
      campus_id: 9,
      course_id: 3,
      material_id: 5,
      start_date: '2026-06-01',
      end_date: '2026-06-30',
    })
  })
})
