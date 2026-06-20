import type { ContentReviewStatus, LearningMaterialRow, ShowcaseRow } from '../../api/content/types.ts'

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
    return error.message ?? 'material requires approved review before publish'
  }
  if (error.code === 422) {
    return error.message ?? 'validation failed'
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
