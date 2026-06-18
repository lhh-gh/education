import { describe, expect, it } from 'vitest'
import { operationTagType } from '../operationRules.ts'

describe('teacher workload report', () => {
  it('substitute_workload_uses_neutral_badge', () => {
    expect(operationTagType('substitute')).toBe('info')
  })
})
