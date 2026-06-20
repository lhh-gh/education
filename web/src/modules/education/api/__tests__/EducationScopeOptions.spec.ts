import { afterEach, describe, expect, it } from 'vitest'
import { clearEducationScope, setEducationScope } from '@/composables/education/useEducationScope.ts'
import { educationScopeGetOptions, educationScopeHeaders, educationScopeRequestOptions } from '../scope.ts'

describe('education api scope options', () => {
  afterEach(() => {
    clearEducationScope()
  })

  it('builds tenant and campus headers from positive scoped params', () => {
    expect(educationScopeHeaders({ tenant_id: 12, campus_id: 34 })).toEqual({
      'X-Tenant-Id': '12',
      'X-Campus-Id': '34',
    })

    expect(educationScopeRequestOptions({ tenant_id: 12, campus_id: 34 })).toEqual({
      headers: {
        'X-Tenant-Id': '12',
        'X-Campus-Id': '34',
      },
    })
  })

  it('omits empty and non-positive scope headers', () => {
    expect(educationScopeHeaders({ tenant_id: 0, campus_id: -1 })).toEqual({})
    expect(educationScopeRequestOptions({})).toEqual({})
  })

  it('keeps get params while adding scope headers', () => {
    const params = { tenant_id: 12, campus_id: 34, keyword: 'math' }

    expect(educationScopeGetOptions(params)).toEqual({
      params,
      headers: {
        'X-Tenant-Id': '12',
        'X-Campus-Id': '34',
      },
    })
  })

  it('fills missing scope headers from shared education context', () => {
    setEducationScope({ tenant_id: 11, campus_id: 22 })

    expect(educationScopeRequestOptions({})).toEqual({
      headers: {
        'X-Tenant-Id': '11',
        'X-Campus-Id': '22',
      },
    })
  })

  it('lets explicit request scope override shared education context', () => {
    setEducationScope({ tenant_id: 11, campus_id: 22 })

    expect(educationScopeHeaders({ tenant_id: 33 })).toEqual({
      'X-Tenant-Id': '33',
      'X-Campus-Id': '22',
    })
  })

  it('keeps get params immutable while adding context scope params and headers', () => {
    setEducationScope({ tenant_id: 11, campus_id: 22 })
    const params = { keyword: 'math' }

    expect(educationScopeGetOptions(params)).toEqual({
      params: {
        keyword: 'math',
        tenant_id: 11,
        campus_id: 22,
      },
      headers: {
        'X-Tenant-Id': '11',
        'X-Campus-Id': '22',
      },
    })
    expect(params).toEqual({ keyword: 'math' })
  })
})
