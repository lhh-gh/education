import type { MineResult } from '../foundation/types.ts'
import type { ContentPage, ContentReviewRow, ContentReviewStatus, ContentScopedParams } from './types.ts'
import { contentGetOptions, contentRequestOptions } from './types.ts'

export interface ContentReviewPayload extends ContentScopedParams {
  status: Exclude<ContentReviewStatus, 'pending'>
  review_note?: string
}

export function pageContentReviews(params: ContentScopedParams): Promise<MineResult<ContentPage<ContentReviewRow>>> {
  return useHttp().get('/admin/education/content/reviews', contentGetOptions(params))
}

export function reviewContent(id: number, payload: ContentReviewPayload): Promise<MineResult<{ review_id: number, status: string }>> {
  return useHttp().post(`/admin/education/content/reviews/${id}/review`, payload, contentRequestOptions(payload))
}
