import { describe, expect, it } from 'vitest'
import { showcaseEditState } from '../contentRules.ts'

describe('showcase list', () => {
  it('asserts_published_showcase_cannot_be_edited_in_place', () => {
    expect(showcaseEditState({ status: 'published' })).toEqual({ canEdit: false, badge: '发布后不可编辑' })
    expect(showcaseEditState({ status: 'draft' })).toEqual({ canEdit: true, badge: '草稿可编辑' })
  })
})
