import { readFileSync } from 'fs'
import { join } from 'path'
import {
  getTeacherGrowthTrialLessons,
  MobileApiError,
  submitTeacherGrowthTrialFeedback,
} from '../../src/api/growth/teacher'

describe('teacher growth mobile', () => {
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

  it('lists_assigned_trial_lessons_using_teacher_scope_only', async () => {
    storageMock.mockImplementation((key: string) => ({
      access_token: 'teacher-token',
      education_tenant_id: 11,
      education_campus_id: 22,
    })[key])
    requestMock.mockImplementation((options) => {
      options.success({
        data: {
          code: 200,
          message: 'success',
          data: { list: [{ id: 401, lead_id: 101, student_name: 'Student Wang', start_time: '2026-06-13 09:00:00', status: 'scheduled' }] },
        },
      })
    })

    await expect(getTeacherGrowthTrialLessons({ date: '2026-06-13' })).resolves.toEqual({
      list: [{ id: 401, lead_id: 101, student_name: 'Student Wang', start_time: '2026-06-13 09:00:00', status: 'scheduled' }],
    })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { date: '2026-06-13' },
      header: { Authorization: 'Bearer teacher-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'GET',
      url: '/mobile/education/admissions/teacher/trial-lessons',
    }))
  })

  it('submits_growth_feedback_and_preserves_unassigned_403', async () => {
    requestMock
      .mockImplementationOnce((options) => {
        options.success({ data: { code: 200, message: 'success', data: { trial_feedback_id: 501 } } })
      })
      .mockImplementationOnce((options) => {
        options.success({ data: { code: 403, message: 'trial lesson is not assigned to current teacher', data: { trial_lesson_id: 402 } } })
      })

    await expect(submitTeacherGrowthTrialFeedback({
      trial_lesson_id: 401,
      classroom_performance: 'focused',
      course_recommendation: 'beginner art',
      teacher_note: 'good fit',
    })).resolves.toEqual({ trial_feedback_id: 501 })

    let thrown: MobileApiError | undefined
    try {
      await submitTeacherGrowthTrialFeedback({
        trial_lesson_id: 402,
        classroom_performance: 'focused',
        course_recommendation: 'beginner art',
        teacher_note: 'good fit',
      })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(403)
    expect(thrown?.data).toEqual({ trial_lesson_id: 402 })
    expect(requestMock).toHaveBeenNthCalledWith(1, expect.objectContaining({
      data: {
        trial_lesson_id: 401,
        classroom_performance: 'focused',
        course_recommendation: 'beginner art',
        teacher_note: 'good fit',
      },
      method: 'POST',
      url: '/mobile/education/growth/teacher/trial-feedback',
    }))
  })

  it('growth_pages_are_registered_and_do_not_accept_teacher_id_override', () => {
    const listSource = readFileSync(join(process.cwd(), 'src/pages/teacher/growth/trial-lessons.vue'), 'utf8')
    const feedbackSource = readFileSync(join(process.cwd(), 'src/pages/teacher/growth/trial-feedback.vue'), 'utf8')
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string, meta?: { role?: string, requiresProfile?: string } }>
    const paths = pages.map(page => page.path)

    expect(paths).toEqual(expect.arrayContaining([
      'pages/teacher/growth/trial-lessons',
      'pages/teacher/growth/trial-feedback',
    ]))
    expect(pages.filter(page => page.path.startsWith('pages/teacher/growth/')).every(page => page.meta?.role === 'teacher')).toBe(true)
    expect(listSource).toContain('getTeacherGrowthTrialLessons({ date: state.date })')
    expect(feedbackSource).toContain("state.status = isForbidden(error) ? 'forbidden' : 'error'")
    expect(listSource).not.toContain('teacher_id')
    expect(feedbackSource).not.toContain('teacher_id')
  })
})
