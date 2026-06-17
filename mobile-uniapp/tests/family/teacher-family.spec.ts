import { readFileSync } from 'fs'
import { join } from 'path'
import {
  createTeacherLessonComment,
  getTeacherCommentTemplates,
  MobileApiError,
} from '../../src/api/family/teacher'

describe('teacher family api and pages', () => {
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

  it('unassigned_lesson_comment_preserves_403_state', async () => {
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
      await createTeacherLessonComment({ lesson_id: 8801, student_id: 1201, content: 'good', publish: true })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(403)
    expect(thrown?.message).toBe('teacher is not assigned to this lesson student')
    expect(thrown?.data).toEqual({ lesson_id: 8801, student_id: 1201 })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { lesson_id: 8801, student_id: 1201, content: 'good', publish: true },
      header: { Authorization: 'Bearer teacher-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'POST',
      url: '/mobile/education/family/teacher/lesson-comments',
    }))
  })

  it('template_selection_fills_comment_content', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/teacher/family/comment-form.vue'), 'utf8')

    expect(source).toContain('function applyTemplate')
    expect(source).toContain('state.content = state.templates[index]?.content || state.content')
    expect(getTeacherCommentTemplates).toEqual(expect.any(Function))
  })

  it('teacher_family_pages_are_registered', () => {
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string, meta?: { role?: string } }>
    const familyPages = pages.filter(page => page.path.startsWith('pages/teacher/family/'))

    expect(familyPages.map(page => page.path)).toEqual(expect.arrayContaining([
      'pages/teacher/family/lesson-comments',
      'pages/teacher/family/comment-form',
      'pages/teacher/family/homework-reviews',
      'pages/teacher/family/growth-record-form',
      'pages/teacher/family/messages',
    ]))
    expect(familyPages.every(page => page.meta?.role === 'teacher')).toBe(true)
  })
})
