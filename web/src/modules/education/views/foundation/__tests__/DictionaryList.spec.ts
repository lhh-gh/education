import { describe, expect, it } from 'vitest'
import { dictionaryActionsByPermission, dictionaryItemParamsForType, dictionaryOwnerTypeOptions } from '../actionRules.ts'

describe('dictionaryList', () => {
  it('selecting_type_loads_items', () => {
    const params = dictionaryItemParamsForType({ page: 3, page_size: 50, keyword: 'level' }, {
      id: 12,
      owner_type: 'tenant',
      owner_key: 'tenant:1001',
      code: 'student.level',
      name: 'Student level',
      status: 'enabled',
      is_locked: false,
      sort_order: 0,
    })

    expect(params).toMatchObject({
      page: 3,
      page_size: 50,
      keyword: 'level',
      dict_type_id: 12,
      dict_code: 'student.level',
    })
  })

  it('locked_rows_hide_mutation_buttons', () => {
    const actions = dictionaryActionsByPermission([
      'education:foundation:dictionary:create',
      'education:foundation:dictionary:update',
      'education:foundation:dictionary:status',
      'education:foundation:dictionary:delete',
      'education:foundation:dictionary-item:create',
      'education:foundation:dictionary-item:update',
      'education:foundation:dictionary-item:status',
      'education:foundation:dictionary-item:delete',
    ], true, 'enabled', 'disabled')

    expect(actions.canCreateType).toBe(true)
    expect(actions.canCreateItem).toBe(true)
    expect(actions.canEditType).toBe(false)
    expect(actions.typeStatusAction).toBeNull()
    expect(actions.canDeleteType).toBe(false)
    expect(actions.canEditItem).toBe(false)
    expect(actions.itemStatusAction).toBeNull()
    expect(actions.canDeleteItem).toBe(false)

    const platformActions = dictionaryActionsByPermission(['education:*'], true, 'enabled', 'disabled')

    expect(platformActions.canEditType).toBe(true)
    expect(platformActions.typeStatusAction).toBe('disable')
    expect(platformActions.canDeleteType).toBe(true)
    expect(platformActions.canEditItem).toBe(true)
    expect(platformActions.itemStatusAction).toBe('enable')
    expect(platformActions.canDeleteItem).toBe(true)
  })

  it('tenant user cannot choose owner_type system', () => {
    expect(dictionaryOwnerTypeOptions(false).map(option => option.value)).toEqual(['tenant'])
  })
})
