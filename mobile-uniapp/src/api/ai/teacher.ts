import type { OperationScopedParams } from '../operations/shared'
import { requestOperation } from '../operations/shared'
import { createTeacherLessonComment } from '../family/teacher'

export { MobileApiError } from '../operations/shared'

export type TeacherAiDraftStatus = 'queued' | 'running' | 'succeeded' | 'blocked' | 'failed'

export interface TeacherCommentDraftPayload extends OperationScopedParams {
  lesson_id: number
  student_id: number
  keywords?: string[]
}

export interface TeacherCommentDraftResult {
  task_id: number
  status: TeacherAiDraftStatus
}

export interface TeacherCommentDraftSavePayload extends OperationScopedParams {
  lesson_id: number
  student_id: number
  content: string
  publish?: boolean
}

export function requestTeacherLessonCommentDraft(payload: TeacherCommentDraftPayload): Promise<TeacherCommentDraftResult> {
  return requestOperation('/mobile/education/ai/teacher/lesson-comment-drafts', 'POST', payload)
}

export function saveAiDraftAsLessonComment(payload: TeacherCommentDraftSavePayload): Promise<{ lesson_comment_id: number, status: 'draft' | 'published' | 'withdrawn' }> {
  return createTeacherLessonComment({ ...payload, publish: payload.publish ?? false })
}

export function isActiveDraftStatus(status: TeacherAiDraftStatus): boolean {
  return status === 'queued' || status === 'running'
}

export function aiDraftStatusMessage(status: TeacherAiDraftStatus): string {
  if (status === 'blocked') {
    return 'AI draft was blocked by safety policy'
  }
  if (status === 'failed') {
    return 'AI draft failed'
  }
  if (status === 'succeeded') {
    return 'AI draft is ready'
  }

  return 'AI draft is being generated'
}
