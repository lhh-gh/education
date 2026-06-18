import type { ContentReviewStatus, LearningMaterialRow, ShowcaseRow } from '../../api/content/types.ts'

export function guardianVisibleLabel(row: Pick<LearningMaterialRow, 'guardian_visible'>): string {
  return row.guardian_visible ? 'Guardian visible' : 'Internal only'
}

export function materialPublishState(row: Pick<LearningMaterialRow, 'status'>): { canPublish: boolean, badge: string } {
  if (row.status === 'published') {
    return { canPublish: false, badge: 'Published' }
  }
  if (row.status === 'withdrawn') {
    return { canPublish: true, badge: 'Withdrawn' }
  }

  return { canPublish: true, badge: 'Review required' }
}

export function publishFailureNotice(error: { code?: number, message?: string }): string {
  if (error.code === 409) {
    return error.message ?? 'material requires approved review before publish'
  }
  if (error.code === 422) {
    return error.message ?? 'validation failed'
  }
  if (error.code === 403) {
    return 'Permission denied'
  }

  return error.message ?? 'Publish failed'
}

export function reviewSubmitState(input: { status?: ContentReviewStatus, review_note?: string }): { disabled: boolean, message: string } {
  if (input.status === 'rejected' && !input.review_note?.trim()) {
    return { disabled: true, message: 'Review note is required when rejecting' }
  }
  if (input.status === 'approved' || input.status === 'rejected') {
    return { disabled: false, message: 'Ready' }
  }

  return { disabled: true, message: 'Select approve or reject' }
}

export function showcaseEditState(row: Pick<ShowcaseRow, 'status'>): { canEdit: boolean, badge: string } {
  if (row.status === 'published') {
    return { canEdit: false, badge: 'Immutable after publish' }
  }

  return { canEdit: true, badge: 'Draft editable' }
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
