import { describe, expect, it } from 'vitest'
import { dataScopePreviewText } from '../groupRules.ts'

describe('data permission list', () => {
  it('scope_preview_displays_campus_ids_returned_by_api', () => {
    expect(dataScopePreviewText({ allowed_campus_ids: [2001, 2002] })).toBe('2001, 2002')
    expect(dataScopePreviewText({ allowed_campus_ids: [] })).toBe('暂无校区范围')
  })
})
