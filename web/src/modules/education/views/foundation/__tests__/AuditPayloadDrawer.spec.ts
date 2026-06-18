import type { AuditLogDetail } from '../../../api/foundation/auditLog.ts'
import { describe, expect, it } from 'vitest'
import { auditPayloadSections, formatAuditPayload } from '../components/auditPayloadRules.ts'

describe('audit payload drawer', () => {
  it('renders_before_after_diff_and_metadata', () => {
    const detail = {
      before_snapshot: { name: 'Campus East' },
      after_snapshot: { name: 'Campus East Plus' },
      diff: { name: { before: 'Campus East', after: 'Campus East Plus' } },
      metadata: { campus_id: 2001 },
    } as AuditLogDetail

    expect(auditPayloadSections(detail).map(section => section.title)).toEqual(['变更前', '变更后', '差异', '元数据'])
    expect(formatAuditPayload(detail.diff)).toContain('"before": "Campus East"')
    expect(formatAuditPayload(detail.metadata)).toContain('"campus_id": 2001')
  })

  it('shows_empty_state_for_null_payload', () => {
    const sections = auditPayloadSections(null)

    expect(sections.every(section => section.value === null)).toBe(true)
    expect(formatAuditPayload(null)).toBe('')
  })
})
