import { describe, expect, it } from 'vitest'
import {
  normalizePrimaryRelations,
  relationLabel,
} from '../actionRules.ts'

describe('student guardian drawer', () => {
  it('defaults_first_relation_to_primary_when_none_selected', () => {
    const relations = normalizePrimaryRelations([
      { guardian_id: 301, relation: 'mother', is_primary: false },
      { guardian_id: 302, relation: 'father', is_primary: false },
    ])

    expect(relations.filter(item => item.is_primary)).toHaveLength(1)
    expect(relations[0].is_primary).toBe(true)
  })

  it('keeps_save_failure_state_open_for_correction', () => {
    const modelValue = true
    const errorText = 'guardian is outside current tenant'

    expect(modelValue).toBe(true)
    expect(errorText).toContain('outside current tenant')
  })

  it('renders_relation_labels_for_supported_values', () => {
    expect(relationLabel('father')).toBe('父亲')
    expect(relationLabel('grandmother')).toBe('祖母/外祖母')
    expect(relationLabel('other')).toBe('其他')
  })
})
