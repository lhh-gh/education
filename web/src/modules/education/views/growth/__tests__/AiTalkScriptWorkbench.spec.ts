import { describe, expect, it } from 'vitest'
import { aiScriptPayload, containsBlockedAiPromise } from '../growthRules.ts'

describe('ai talk script workbench', () => {
  it('blocks_automatic_discount_promises_before_submit', () => {
    expect(containsBlockedAiPromise('guaranteed discount today')).toBe(true)
    expect(containsBlockedAiPromise('automatic discount after payment')).toBe(true)
    expect(containsBlockedAiPromise('invite the guardian to trial lesson')).toBe(false)
  })

  it('normalizes_script_generation_payload', () => {
    expect(aiScriptPayload({ tenant_id: 1, campus_id: 2, lead_id: 11, script_type: 'trial_invitation', goal: ' invite trial ' })).toEqual({
      tenant_id: 1,
      campus_id: 2,
      lead_id: 11,
      script_type: 'trial_invitation',
      goal: 'invite trial',
    })
  })
})
