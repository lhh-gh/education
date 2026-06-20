import type { MinePage, PageParams } from '../foundation/types.ts'
import { educationScopeGetOptions, educationScopeRequestOptions } from '../scope.ts'

export type ContentPublishStatus = 'draft' | 'reviewing' | 'published' | 'withdrawn' | 'archived'
export type ContentReviewStatus = 'pending' | 'approved' | 'rejected'

export interface ContentScopedParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  course_id?: number
  material_id?: number
  student_id?: number
  teacher_id?: number
  status?: string
  start_date?: string
  end_date?: string
}

export interface ContentPage<T> extends MinePage<T> {}

export interface LearningMaterialRow {
  id: number
  material_code: string
  material_name: string
  course_id?: number
  material_type: string
  status: ContentPublishStatus
  guardian_visible: boolean
  current_version_id?: number
  summary?: string
}

export interface MaterialVersionRow {
  id: number
  material_id: number
  version_no: number
  title: string
  status: ContentPublishStatus
  published_at?: string
}

export interface MaterialRelationRow {
  id: number
  material_id: number
  target_type: string
  target_id: number
  relation_note?: string
}

export interface StudentWorkRow {
  id: number
  student_id: number
  lesson_id?: number
  teacher_id: number
  title: string
  status: ContentPublishStatus
  published_at?: string
}

export interface ShowcaseRow {
  id: number
  student_id: number
  stage_goal_id?: number
  title: string
  status: ContentPublishStatus
  published_at?: string
}

export interface ContentReviewRow {
  id: number
  business_type: string
  business_id: number
  reviewer_id: number
  status: ContentReviewStatus
  review_note?: string
  reviewed_at?: string
}

export interface MaterialUsageMetricRow {
  metric_date: string
  material_id?: number
  course_id?: number
  teacher_use_count: number
  guardian_read_count: number
  favorite_count: number
}

export interface StudentWorkMetricRow {
  metric_date: string
  student_id?: number
  teacher_id?: number
  created_count: number
  published_count: number
  showcase_count: number
  guardian_read_count: number
}

export function contentRequestOptions(input: { tenant_id?: number, campus_id?: number } = {}): { headers?: Record<string, string> } {
  return educationScopeRequestOptions(input)
}

export function contentGetOptions<T extends ContentScopedParams>(params: T): { params: T, headers?: Record<string, string> } {
  return educationScopeGetOptions(params)
}
