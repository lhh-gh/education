import { readFileSync } from 'fs'
import { join } from 'path'
import {
  aiDraftStatusMessage,
  isActiveDraftStatus,
  MobileApiError,
  requestTeacherLessonCommentDraft,
  saveAiDraftAsLessonComment,
} from '../../src/api/ai/teacher'

describe('teacher ai api and pages', () => {
  const requestMock = jest.fn()
  const storageMock = jest.fn()

  beforeEach(() => {
    requestMock.mockReset()
    storageMock.mockReset()
    ;(global as any).uni = {
      getStorageSync: storageMock,
      request: requestMock,
    }
  })

  afterEach(() => {
    delete (global as any).uni
  })

  it('teacher_draft_request_requires_assigned_lesson_student_scope', async () => {
    storageMock.mockImplementation((key: string) => ({
      access_token: 'teacher-token',
      education_tenant_id: 11,
      education_campus_id: 22,
    })[key])
    requestMock.mockImplementation((options) => {
      options.success({
        data: {
          code: 403,
          message: 'teacher is not assigned to this lesson student',
          data: { lesson_id: 8801, student_id: 1201 },
        },
      })
    })

    let thrown: MobileApiError | undefined
    try {
      await requestTeacherLessonCommentDraft({ lesson_id: 8801, student_id: 1201, keywords: ['active'] })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(403)
    expect(thrown?.message).toBe('teacher is not assigned to this lesson student')
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { lesson_id: 8801, student_id: 1201, keywords: ['active'] },
      header: { Authorization: 'Bearer teacher-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'POST',
      url: '/mobile/education/ai/teacher/lesson-comment-drafts',
    }))
  })

  it('blocked_result_displays_safety_message_and_active_statuses_poll', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/teacher/ai/comment-draft-detail.vue'), 'utf8')

    expect(aiDraftStatusMessage('blocked')).toBe('AI draft was blocked by safety policy')
    expect(isActiveDraftStatus('queued')).toBe(true)
    expect(isActiveDraftStatus('running')).toBe(true)
    expect(isActiveDraftStatus('succeeded')).toBe(false)
    expect(source).toContain('Safety Blocked')
  })

  it('edited_draft_saves_into_v7_comment_draft_flow', async () => {
    requestMock.mockImplementation((options) => {
      options.success({
        data: {
          code: 200,
          message: 'success',
          data: { lesson_comment_id: 501, status: 'draft' },
        },
      })
    })

    await expect(saveAiDraftAsLessonComment({ lesson_id: 8801, student_id: 1201, content: 'Edited draft' })).resolves.toEqual({ lesson_comment_id: 501, status: 'draft' })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { lesson_id: 8801, student_id: 1201, content: 'Edited draft', publish: false },
      method: 'POST',
      url: '/mobile/education/family/teacher/lesson-comments',
    }))
  })

  it('teacher_ai_pages_are_registered', () => {
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string, meta?: { role?: string, requiresProfile?: string } }>
    const aiPages = pages.filter(page => page.path.startsWith('pages/teacher/ai/'))

    expect(aiPages.map(page => page.path)).toEqual(expect.arrayContaining([
      'pages/teacher/ai/comment-drafts',
      'pages/teacher/ai/comment-draft-detail',
    ]))
    expect(aiPages.every(page => page.meta?.role === 'teacher')).toBe(true)
    expect(aiPages.every(page => page.meta?.requiresProfile === 'teacher')).toBe(true)
  })
})
