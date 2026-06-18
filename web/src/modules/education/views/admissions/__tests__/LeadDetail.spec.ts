import { describe, expect, it } from 'vitest'
import { admissionTagType } from '../admissionRules.ts'

describe('lead detail', () => {
  it('renders_follow_timeline_status_styles', () => {
    expect(admissionTagType('converted')).toBe('success')
    expect(admissionTagType('lost')).toBe('danger')
  })
})
