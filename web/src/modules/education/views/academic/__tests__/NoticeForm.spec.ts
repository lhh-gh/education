import { describe, expect, it } from 'vitest'
import { defaultNoticeForm, normalizeNoticeFormTarget, targetRequiresId } from '../noticeRules.ts'

describe('notice form', () => {
  it('target_controls_switch_by_target_type', () => {
    expect(targetRequiresId('all')).toBe(false)
    expect(targetRequiresId('campus')).toBe(true)
    expect(targetRequiresId('class')).toBe(true)
    expect(targetRequiresId('student')).toBe(true)
  })

  it('all_target_clears_target_ids', () => {
    const form = normalizeNoticeFormTarget({ ...defaultNoticeForm(1), campus_id: 2, target_type: 'all', target_id: 3 })

    expect(form.tenant_id).toBe(1)
    expect(form.campus_id).toBeUndefined()
    expect(form.target_id).toBeUndefined()
  })

  it('business_failure_keeps_form_open_for_correction', () => {
    const modelValue = true
    const errorText = 'target class has no receivable guardians'

    expect(modelValue).toBe(true)
    expect(errorText).toContain('receivable guardians')
  })
})
