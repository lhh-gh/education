import type { NoticeRecord, NoticeSavePayload, NoticeStatus, NoticeTargetType } from '../../api/academic/notice.ts'

export function noticeStatusType(status: NoticeStatus): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const map = {
    draft: 'info',
    published: 'success',
    withdrawn: 'warning',
  } as const

  return map[status]
}

export function noticePriorityType(priority: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  if (priority === 'urgent') {
    return 'danger'
  }
  if (priority === 'important') {
    return 'warning'
  }

  return 'info'
}

export function canEditNotice(row: NoticeRecord, hasPermission: boolean): boolean {
  return hasPermission && row.status === 'draft'
}

export function canPublishNotice(row: NoticeRecord, hasPermission: boolean): boolean {
  return hasPermission && row.status === 'draft'
}

export function canWithdrawNotice(row: NoticeRecord, hasPermission: boolean): boolean {
  return hasPermission && row.status === 'published'
}

export function targetRequiresId(targetType: NoticeTargetType): boolean {
  return targetType !== 'all'
}

export function defaultNoticeForm(tenantId?: number): NoticeSavePayload {
  return {
    tenant_id: tenantId,
    campus_id: undefined,
    notice_type: 'academic',
    target_type: 'all',
    target_id: undefined,
    title: '',
    content: '',
    priority: 'normal',
    expire_at: undefined,
    remark: '',
  }
}

export function normalizeNoticeFormTarget(form: NoticeSavePayload): NoticeSavePayload {
  if (form.target_type === 'all') {
    return { ...form, campus_id: undefined, target_id: undefined }
  }

  return form
}

export function receiptReadSummary(receiptCount: number, readCount: number): string {
  return `${readCount}/${receiptCount}`
}

export function noticeStatusLabel(status?: string | null): string {
  const labels: Record<string, string> = {
    draft: '草稿',
    published: '已发布',
    withdrawn: '已撤回',
  }

  return status ? labels[status] ?? status : '未知'
}

export function noticeTypeLabel(type?: string | null): string {
  const labels: Record<string, string> = {
    academic: '教务',
    activity: '活动',
    fee: '费用',
    system: '系统',
  }

  return type ? labels[type] ?? type : '未知'
}

export function noticeTargetTypeLabel(type?: string | null): string {
  const labels: Record<string, string> = {
    all: '全部',
    campus: '校区',
    class: '班级',
    student: '学员',
  }

  return type ? labels[type] ?? type : '未知'
}

export function noticePriorityLabel(priority?: string | null): string {
  const labels: Record<string, string> = {
    normal: '普通',
    important: '重要',
    urgent: '紧急',
  }

  return priority ? labels[priority] ?? priority : '未知'
}
