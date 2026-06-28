import { afterEach, describe, expect, it } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { educationScopeGetOptions } from '../../../api/scope.ts'
import { clearEducationScope } from '@/composables/education/useEducationScope.ts'
import {
  buildCampusScopeOptions,
  buildTenantScopeOptions,
  resolveEducationScopePayload,
  shouldShowEducationScopeContext,
} from '../educationScopeContextRules.ts'

describe('education scope context entry', () => {
  afterEach(() => {
    clearEducationScope()
  })

  it('normalizes tenant and campus options for selector controls', () => {
    expect(buildTenantScopeOptions([
      { id: 1, name: 'Tenant A', code: 'tenant-a', status: 'enabled' },
      { id: 2, name: 'Tenant B', code: 'tenant-b', status: 'disabled' },
    ])).toEqual([
      { label: 'Tenant A（tenant-a）', value: 1, disabled: false },
      { label: 'Tenant B（tenant-b）', value: 2, disabled: true },
    ])

    expect(buildCampusScopeOptions([
      { id: 9, tenant_id: 1, name: 'Main Campus', code: 'main', status: 'enabled' },
    ])).toEqual([
      { label: 'Main Campus（main）', value: 9, disabled: false },
    ])
  })

  it('writes selected tenant and campus into shared api scope', () => {
    const payload = resolveEducationScopePayload(1, 9)

    expect(payload).toEqual({ tenant_id: 1, campus_id: 9 })
    expect(educationScopeGetOptions({ keyword: 'math' }).headers).toEqual({
      'X-Tenant-Id': '1',
      'X-Campus-Id': '9',
    })
  })

  it('mounts education layout as the shared route entry', () => {
    expect(shouldShowEducationScopeContext('/education/academic/dashboard')).toBe(true)
    expect(shouldShowEducationScopeContext('/system/user')).toBe(false)
    expect(educationRoutes[0].component).toBeTypeOf('function')
  })
})
