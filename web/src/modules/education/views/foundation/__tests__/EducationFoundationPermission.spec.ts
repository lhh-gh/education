import { describe, expect, it } from 'vitest'
import { hasAnyEducationPermission, hasEducationPermission } from '@/composables/education/educationPermissionRules.ts'

describe('education foundation permission', () => {
  it('has_supports_exact_and_wildcard_permissions', () => {
    expect(hasEducationPermission([
      'education:foundation:tenant:page',
    ], 'education:foundation:tenant:page')).toBe(true)

    expect(hasEducationPermission([
      'education:*',
    ], 'education:foundation:audit-log:detail')).toBe(true)

    expect(hasEducationPermission([
      '*',
    ], 'education:foundation:feature-flag:update')).toBe(true)

    expect(hasAnyEducationPermission([
      'education:foundation:campus:update',
    ], [
      'education:foundation:tenant:update',
      'education:foundation:campus:update',
    ])).toBe(true)
  })

  it('has_returns_false_for_missing_permission', () => {
    expect(hasEducationPermission([
      'education:foundation:tenant:page',
    ], 'education:foundation:tenant:update')).toBe(false)

    expect(hasAnyEducationPermission([], [
      'education:foundation:tenant:update',
      'education:foundation:campus:update',
    ])).toBe(false)
  })
})
