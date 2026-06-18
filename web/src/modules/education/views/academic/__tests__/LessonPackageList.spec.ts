import { describe, expect, it } from 'vitest'
import {
  computePackageTotal,
  lessonPackageQuery,
} from '../courseAccountRules.ts'

describe('lesson package list', () => {
  it('computes_total_units_in_form', () => {
    expect(computePackageTotal(20, 4)).toBe('24.00')
    expect(computePackageTotal('1.5', '0.5')).toBe('2.00')
  })

  it('validation_failure_keeps_package_form_open', () => {
    expect(lessonPackageQuery({
      page: 1,
      page_size: 20,
      tenant_id: 1001,
      campus_id: 2001,
      course_id: 301,
      keyword: '24',
      status: 'enabled',
      ignored: 'value',
    })).toEqual({
      page: 1,
      page_size: 20,
      tenant_id: 1001,
      campus_id: 2001,
      course_id: 301,
      keyword: '24',
      status: 'enabled',
    })
  })
})
