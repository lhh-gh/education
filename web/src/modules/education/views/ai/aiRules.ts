import type { AiTagType } from '../../api/ai/types.ts'
import type { GenerationStatus } from '../../api/ai/generation.ts'

export interface AiReviewState {
  safety_status: string
  review_status: string
}

export function aiTagType(status?: string): AiTagType {
  if (status === 'succeeded' || status === 'approved' || status === 'enabled' || status === 'published' || status === 'handled') {
    return 'success'
  }
  if (status === 'queued' || status === 'running' || status === 'pending' || status === 'warning') {
    return 'warning'
  }
  if (status === 'blocked' || status === 'failed' || status === 'rejected' || status === 'high') {
    return 'danger'
  }

  return 'info'
}

export function shouldPollGenerationStatus(status: GenerationStatus): boolean {
  return status === 'queued' || status === 'running' || status === 'pending'
}

export function canApproveAiResult(row: AiReviewState): boolean {
  return row.safety_status !== 'blocked' && row.review_status === 'pending'
}

export function containsRawSql(text: string): boolean {
  return /\b(?:select|insert|update|delete|drop|alter|truncate)\b/i.test(text)
}

export function aiErrorTitle(code?: number): string {
  if (code === 403) {
    return 'Permission denied'
  }
  if (code === 422) {
    return 'Validation failed'
  }
  if (code === 409) {
    return 'State conflict'
  }

  return ''
}

export function maskedSecret(): string {
  return '********'
}
