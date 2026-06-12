import { describe, expect, it } from 'vitest'
import { featureFlagActionsByPermission } from '../actionRules.ts'

describe('FeatureFlagList', () => {
  it('renders_feature_flag_status_flow', () => {
    const enabledActions = featureFlagActionsByPermission([
      'education:foundation:feature-flag:status',
      'education:foundation:feature-flag:delete',
    ], true, true)

    expect(enabledActions.enabledAction).toBe('disable')
    expect(enabledActions.canDelete).toBe(false)

    const disabledActions = featureFlagActionsByPermission([
      'education:foundation:feature-flag:status',
      'education:foundation:feature-flag:delete',
    ], false, false)

    expect(disabledActions.enabledAction).toBe('enable')
    expect(disabledActions.canDelete).toBe(true)
  })
})
