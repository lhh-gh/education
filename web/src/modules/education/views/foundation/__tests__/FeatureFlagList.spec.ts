import { describe, expect, it } from 'vitest'
import {
  featureFlagActionsByPermission,
  normalizeFeatureFlagSearch,
  parseJsonObjectText,
  shouldCloseFormAfterSubmit,
} from '../actionRules.ts'

describe('featureFlagList', () => {
  it('renders_feature_flag_status_flow', () => {
    const enabledActions = featureFlagActionsByPermission([
      'education:foundation:feature-flag:status',
      'education:foundation:feature-flag:delete',
    ], true, true)

    expect(enabledActions.enabledAction).toBeNull()
    expect(enabledActions.statusAction).toBeNull()
    expect(enabledActions.canDelete).toBe(false)

    const disabledActions = featureFlagActionsByPermission([
      'education:foundation:feature-flag:status',
      'education:foundation:feature-flag:delete',
    ], false, false)

    expect(disabledActions.enabledAction).toBe('enable')
    expect(disabledActions.canDelete).toBe(true)

    const platformActions = featureFlagActionsByPermission(['education:*'], true, true, 'enabled')

    expect(platformActions.canEdit).toBe(true)
    expect(platformActions.enabledAction).toBe('disable')
    expect(platformActions.statusAction).toBe('disable')
    expect(platformActions.canDelete).toBe(true)
  })

  it('search_maps_date_range_to_params', () => {
    const params = normalizeFeatureFlagSearch({
      page: 2,
      page_size: 50,
      owner_type: 'tenant',
      tenant_id: 1001,
      keyword: 'guardian',
      enabled: true,
      status: 'enabled',
    }, ['2026-06-10 00:00:00', '2026-06-30 23:59:59'])

    expect(params).toMatchObject({
      page: 2,
      page_size: 50,
      owner_type: 'tenant',
      tenant_id: 1001,
      keyword: 'guardian',
      enabled: true,
      status: 'enabled',
      effective_from: '2026-06-10 00:00:00',
      effective_to: '2026-06-30 23:59:59',
    })
  })

  it('config_validation_failure_keeps_form_open', () => {
    expect(() => parseJsonObjectText('[]', 'config')).toThrow('config 必须是 JSON 对象')
    expect(shouldCloseFormAfterSubmit(false)).toBe(false)
    expect(shouldCloseFormAfterSubmit(true)).toBe(true)
  })
})
