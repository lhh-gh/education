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

export function familyStatusLabel(status?: string): string {
  const labels: Record<string, string> = {
    assigned: '已布置',
    disabled: '停用',
    draft: '草稿',
    enabled: '启用',
    overdue: '已逾期',
    published: '已发布',
    reviewed: '已点评',
    sent: '已发送',
    submitted: '已提交',
    withdrawn: '已撤回',
  }

  return status ? labels[status] ?? status : ''
}

export function familySenderLabel(sender?: string): string {
  const labels: Record<string, string> = {
    guardian: '家长',
    parent: '家长',
    student: '学员',
    system: '系统',
    teacher: '教师',
  }

  return sender ? labels[sender] ?? sender : ''
}

export function familyRecordTypeLabel(type?: string): string {
  const labels: Record<string, string> = {
    attendance: '考勤',
    class: '课堂',
    comment: '评语',
    homework: '作业',
    report: '报告',
    summary: '总结',
  }

  return type ? labels[type] ?? type : ''
}

export function targetCountLabel(record: Pick<FamilyStatusRecord, 'target_count'>): string {
  return `${record.target_count ?? 0} 人`
}

export function homeworkPublishState(row: FamilyStatusRecord, result: { status: string, target_count?: number }): FamilyStatusRecord {
  return {
    ...row,
    status: result.status,
    target_count: result.target_count ?? row.target_count,
  }
}

export function guardianVisibleMarker(row: Pick<FamilyStatusRecord, 'status'>): string {
  return row.status === 'published' ? '家长可见' : ''
}

export function reportStatusAfterWithdraw(row: FamilyStatusRecord): string {
  return row.id ? familyStatusLabel('withdrawn') : familyStatusLabel(row.status ?? 'withdrawn')
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

export function familyMetricCards(metrics: Record<string, number> = {}): Array<{ title: string, value: number }> {
  return [
    { title: '评语数', value: metrics.comment_count ?? 0 },
    { title: '作业点评', value: metrics.homework_review_count ?? 0 },
    { title: '学习报告', value: metrics.report_count ?? 0 },
  ]
}
