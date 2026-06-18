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
