import { describe, expect, it } from 'vitest'
import { dictionaryActionsByPermission, dictionaryOwnerTypeOptions } from '../actionRules.ts'

describe('DictionaryList', () => {
  it('renders_dictionary_actions_by_permission', () => {
    const actions = dictionaryActionsByPermission([
      'education:foundation:dictionary:create',
      'education:foundation:dictionary:delete',
      'education:foundation:dictionary-item:create',
      'education:foundation:dictionary-item:delete',
    ], true)

    expect(actions.canCreateType).toBe(true)
    expect(actions.canCreateItem).toBe(true)
    expect(actions.canDeleteType).toBe(false)
    expect(actions.canDeleteItem).toBe(false)
  })

  it('tenant user cannot choose owner_type system', () => {
    expect(dictionaryOwnerTypeOptions(false).map(option => option.value)).toEqual(['tenant'])
  })
})
