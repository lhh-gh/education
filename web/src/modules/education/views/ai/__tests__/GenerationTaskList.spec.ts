import { describe, expect, it } from 'vitest'
import { shouldPollGenerationStatus } from '../aiRules.ts'

describe('generation task list', () => {
  it('polls_only_active_generation_statuses', () => {
    expect(shouldPollGenerationStatus('queued')).toBe(true)
    expect(shouldPollGenerationStatus('running')).toBe(true)
    expect(shouldPollGenerationStatus('pending')).toBe(true)
    expect(shouldPollGenerationStatus('succeeded')).toBe(false)
    expect(shouldPollGenerationStatus('blocked')).toBe(false)
  })
})
