import { describe, expect, it } from 'vitest'
import { homeworkPublishState, targetCountLabel } from '../familyRules.ts'

describe('homework assignment list', () => {
  it('asserts_target_count_and_publish_state_update_after_api_success', () => {
    expect(targetCountLabel({ target_count: 3 })).toBe('3 人')
    expect(homeworkPublishState({ id: 1, status: 'draft' }, { status: 'published', target_count: 3 })).toMatchObject({
      status: 'published',
      target_count: 3,
    })
  })
})
