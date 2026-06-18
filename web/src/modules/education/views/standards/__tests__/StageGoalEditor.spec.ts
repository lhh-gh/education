import { describe, expect, it } from 'vitest'
import { stageGoalPayload } from '../standardRules.ts'

describe('stage goal editor', () => {
  it('keeps_ability_point_relation_payload_explicit', () => {
    expect(stageGoalPayload({
      tenant_id: 1,
      campus_id: 2,
      service_package_id: 3,
      goal_code: 'S1',
      goal_name: 'Line',
      goal_content: 'control',
      ability_point_ids: [7, 8],
    })).toEqual({
      tenant_id: 1,
      campus_id: 2,
      service_package_id: 3,
      goal_code: 'S1',
      goal_name: 'Line',
      goal_content: 'control',
      ability_point_ids: [7, 8],
    })
  })
})
