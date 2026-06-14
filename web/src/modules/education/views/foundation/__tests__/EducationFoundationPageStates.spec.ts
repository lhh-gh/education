import { describe, expect, it } from 'vitest'
import { isSubmitDisabled, keepListStateAfterError } from '../actionRules.ts'

describe('educationFoundationPageStates', () => {
  it('list_pages_keep_filters_after_error', () => {
    const filters = {
      page: 2,
      page_size: 50,
      keyword: 'campus east',
      status: 'enabled',
    }
    const rows = [{ id: 1, name: 'Campus East' }]

    const state = keepListStateAfterError({ filters, rows, total: 1 })

    expect(state.filters).toBe(filters)
    expect(state.rows).toBe(rows)
    expect(state.total).toBe(1)
  })

  it('forms_disable_submit_while_pending', () => {
    expect(isSubmitDisabled(true)).toBe(true)
    expect(isSubmitDisabled(false)).toBe(false)
  })
})
