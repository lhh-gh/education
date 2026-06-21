import type { ContentPublishStatus, ContentReviewStatus, LearningMaterialRow, ShowcaseRow } from '../../api/content/types.ts'

export function contentStatusLabel(status?: string): string {
  const labels: Record<string, string> = {
    draft: '草稿',
    reviewing: '审核中',
    published: '已发布',
    withdrawn: '已撤回',
    archived: '已归档',
    pending: '待审核',
    approved: '已通过',
    rejected: '已驳回',
  }

  return status ? labels[status] ?? status : '-'
}

export function contentStatusTag(status?: string): 'success' | 'warning' | 'danger' | 'info' {
  if (status === 'published' || status === 'approved') {
    return 'success'
  }
  if (status === 'reviewing' || status === 'pending') {
    return 'warning'
  }
  if (status === 'rejected') {
    return 'danger'
  }

  return 'info'
}

export function guardianVisibleLabel(row: Pick<LearningMaterialRow, 'guardian_visible'>): string {
  return row.guardian_visible ? '家长可见' : '内部使用'
}

export function materialPublishState(row: Pick<LearningMaterialRow, 'status'>): { canPublish: boolean, badge: string } {
  if (row.status === 'published') {
    return { canPublish: false, badge: '已发布' }
  }
  if (row.status === 'withdrawn') {
    return { canPublish: true, badge: '已撤回' }
  }

  return { canPublish: true, badge: '待审核' }
}

export function publishFailureNotice(error: { code?: number, message?: string }): string {
  if (error.code === 409) {
    return error.message ?? '资料需要审核通过后才能发布'
  }
  if (error.code === 422) {
    return error.message ?? '请检查提交内容'
  }
  if (error.code === 403) {
    return '暂无操作权限'
  }

  return error.message ?? '发布失败'
}

export function reviewSubmitState(input: { status?: ContentReviewStatus, review_note?: string }): { disabled: boolean, message: string } {
  if (input.status === 'rejected' && !input.review_note?.trim()) {
    return { disabled: true, message: '驳回时必须填写审核意见' }
  }
  if (input.status === 'approved' || input.status === 'rejected') {
    return { disabled: false, message: '可提交' }
  }

  return { disabled: true, message: '请选择通过或驳回' }
}

export function showcaseEditState(row: Pick<ShowcaseRow, 'status'>): { canEdit: boolean, badge: string } {
  if (row.status === 'published') {
    return { canEdit: false, badge: '发布后不可编辑' }
  }

  return { canEdit: true, badge: '草稿可编辑' }
}

export function metricFilterPayload(input: { tenant_id?: number, campus_id?: number, course_id?: number, material_id?: number, dateRange?: [string, string] }) {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    course_id: input.course_id,
    material_id: input.material_id,
    start_date: input.dateRange?.[0],
    end_date: input.dateRange?.[1],
  }
}

export const contentPublishStatusOptions: Array<{ label: string, value: ContentPublishStatus }> = [
  { label: '草稿', value: 'draft' },
  { label: '审核中', value: 'reviewing' },
  { label: '已发布', value: 'published' },
  { label: '已撤回', value: 'withdrawn' },
  { label: '已归档', value: 'archived' },
]

export const contentReviewStatusOptions: Array<{ label: string, value: ContentReviewStatus }> = [
  { label: '待审核', value: 'pending' },
  { label: '已通过', value: 'approved' },
  { label: '已驳回', value: 'rejected' },
]
