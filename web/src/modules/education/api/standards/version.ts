import type { MineResult } from '../foundation/types.ts'
import type { StandardsScopedParams, StandardVersionRow } from './types.ts'
import { standardsGetOptions, standardsRequestOptions } from './types.ts'

export interface StandardVersionActionPayload extends StandardsScopedParams {
  publish_note?: string
  review_note?: string
  status?: 'approved' | 'rejected'
  override_json?: Record<string, unknown>
}

export function pageStandardVersions(params: StandardsScopedParams): Promise<MineResult<{ list: StandardVersionRow[] }>> {
  return useHttp().get('/admin/education/standards/versions', standardsGetOptions(params))
}

export function publishStandardVersion(id: number, payload: StandardVersionActionPayload): Promise<MineResult<{ standard_version_id: number, status: string }>> {
  return useHttp().post(`/admin/education/standards/versions/${id}/publish`, payload, standardsRequestOptions(payload))
}

export function withdrawStandardVersion(id: number, payload: StandardVersionActionPayload): Promise<MineResult<{ standard_version_id: number, status: string }>> {
  return useHttp().post(`/admin/education/standards/versions/${id}/withdraw`, payload, standardsRequestOptions(payload))
}

export function reviewStandardVersion(id: number, payload: StandardVersionActionPayload): Promise<MineResult<{ review_record_id: number, status: string }>> {
  return useHttp().post(`/admin/education/standards/reviews/${id}/review`, payload, standardsRequestOptions(payload))
}

export function saveLocalizationOverride(id: number, payload: StandardVersionActionPayload): Promise<MineResult<{ localization_override_id: number, status: string }>> {
  return useHttp().post(`/admin/education/standards/versions/${id}/localization-overrides`, payload, standardsRequestOptions(payload))
}
