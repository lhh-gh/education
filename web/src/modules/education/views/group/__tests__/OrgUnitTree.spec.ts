import { describe, expect, it } from 'vitest'
import { keepOrgTreeAfterCycleError } from '../groupRules.ts'

describe('org unit tree', () => {
  it('cycle_error_is_shown_and_tree_does_not_mutate', () => {
    const tree = [{ id: 1, name: 'HQ', children: [{ id: 2, name: 'East' }] }]
    const result = keepOrgTreeAfterCycleError(tree, { code: 409, message: 'org unit parent creates cycle' })

    expect(result.rows).toBe(tree)
    expect(result.errorText).toBe('org unit parent creates cycle')
  })
})
