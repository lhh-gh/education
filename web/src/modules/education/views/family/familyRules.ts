import type { FamilyTagType } from '../../api/family/types.ts'

export interface FamilyStatusRecord {
  id?: number
  status?: string
  target_count?: number
}

export function familyTagType(status?: string): FamilyTagType {
  if (status === 'published' || status === 'reviewed' || status === 'enabled' || status === 'sent') {
    return 'success'
  }
  if (status === 'submitted' || status === 'assigned' || status === 'draft') {
    return 'warning'
  }
  if (status === 'withdrawn' || status === 'overdue' || status === 'disabled') {
    return 'danger'
  }

  return ''
}

export function targetCountLabel(record: Pick<FamilyStatusRecord, 'target_count'>): string {
  return `${record.target_count ?? 0} targets`
}

export function homeworkPublishState(row: FamilyStatusRecord, result: { status: string, target_count?: number }): FamilyStatusRecord {
  return {
    ...row,
    status: result.status,
    target_count: result.target_count ?? row.target_count,
  }
}

export function guardianVisibleMarker(row: Pick<FamilyStatusRecord, 'status'>): string {
  return row.status === 'published' ? 'Guardian visible' : ''
}

export function reportStatusAfterWithdraw(row: FamilyStatusRecord): string {
  return row.id ? 'withdrawn' : (row.status ?? 'withdrawn')
}

export function messageReplyPayload(thread: { student_id: number, thread_id: string }, content: string): { student_id: number, thread_id: string, content: string } {
  return {
    student_id: thread.student_id,
    thread_id: thread.thread_id,
    content,
  }
}

export function qualityFilterParams(filters: { campus_id?: number, teacher_id?: number, student_id?: number, start_date?: string, end_date?: string }): Record<string, number | string> {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== undefined && value !== '')) as Record<string, number | string>
}
