import { describe, expect, it } from 'vitest'
import { educationScopeGetOptions, educationScopeHeaders, educationScopeRequestOptions } from '../scope.ts'

describe('education api scope options', () => {
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
})
