import type { AuditLogDetail } from '../../../api/foundation/auditLog.ts'

export interface AuditPayloadSection {
  key: 'before_snapshot' | 'after_snapshot' | 'diff' | 'metadata'
  title: string
  value: Record<string, unknown> | null
}

export function formatAuditPayload(value: unknown): string {
  if (value === null || value === undefined) {
    return ''
  }

  return JSON.stringify(value, null, 2)
}

export function auditPayloadSections(detail: AuditLogDetail | null): AuditPayloadSection[] {
  return [
    {
      key: 'before_snapshot',
      title: 'Before',
      value: detail?.before_snapshot ?? null,
    },
    {
      key: 'after_snapshot',
      title: 'After',
      value: detail?.after_snapshot ?? null,
    },
    {
      key: 'diff',
      title: 'Diff',
      value: detail?.diff ?? null,
    },
    {
      key: 'metadata',
      title: 'Metadata',
      value: detail?.metadata ?? null,
    },
  ]
}
