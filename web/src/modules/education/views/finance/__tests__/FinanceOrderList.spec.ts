import { describe, expect, it } from 'vitest'
import { canShowOfflineCollection, centsToYuan } from '../financeRules.ts'

describe('finance order list', () => {
  it('offline_collection_button_hides_without_permission_and_formats_cents', () => {
    expect(canShowOfflineCollection(() => false)).toBe(false)
    expect(canShowOfflineCollection(code => code === 'education:finance:payment:offline')).toBe(true)
    expect(centsToYuan(240000)).toBe('¥2400.00')
  })
})
