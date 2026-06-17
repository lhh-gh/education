import type { MineResult } from '../foundation/types.ts'
import type { FamilyPage, FamilyScopedParams } from './types.ts'
import { familyGetOptions, familyRequestOptions } from './types.ts'

export interface CommentTemplateRecord {
  id: number
  template_code: string
  template_name: string
  content: string
  course_id?: number
  status: string
  sort_order?: number
}

export interface CommentTemplatePayload extends FamilyScopedParams {
  id?: number
  template_code: string
  template_name: string
  content: string
  course_id?: number
  status?: string
  sort_order?: number
}

export interface PerformanceTagRecord {
  id: number
  tag_code: string
  tag_name: string
  tag_type: string
  status: string
}

export interface PerformanceTagPayload extends FamilyScopedParams {
  id?: number
  tag_code: string
  tag_name: string
  tag_type: string
  status?: string
  sort_order?: number
}

export function pageCommentTemplates(params: FamilyScopedParams): Promise<MineResult<FamilyPage<CommentTemplateRecord>>> {
  return useHttp().get('/admin/education/family/comment-templates/page', familyGetOptions(params))
}

export function saveCommentTemplate(payload: CommentTemplatePayload): Promise<MineResult<CommentTemplateRecord>> {
  return useHttp().post('/admin/education/family/comment-templates', payload, familyRequestOptions(payload))
}

export function pagePerformanceTags(params: FamilyScopedParams): Promise<MineResult<FamilyPage<PerformanceTagRecord>>> {
  return useHttp().get('/admin/education/family/performance-tags/page', familyGetOptions(params))
}

export function savePerformanceTag(payload: PerformanceTagPayload): Promise<MineResult<PerformanceTagRecord>> {
  return useHttp().post('/admin/education/family/performance-tags', payload, familyRequestOptions(payload))
}
