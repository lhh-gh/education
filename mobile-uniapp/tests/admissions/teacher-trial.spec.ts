import { readFileSync } from 'fs'
import { join } from 'path'
import {
  getTeacherTrialLessons,
  MobileApiError,
  submitTeacherTrialFeedback,
} from '../../src/api/admissions/teacher'

describe('teacher admissions trial pages', () => {
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

  it('renders_assigned_trial_lessons_from_teacher_scoped_api', async () => {
    storageMock.mockImplementation((key: string) => ({ access_token: 'teacher-token', education_tenant_id: 11, education_campus_id: 22 })[key])
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { list: [{ id: 401, lead_id: 101, student_name: 'Student Wang', start_time: '2026-06-13 09:00:00', status: 'scheduled' }] } } })
    })

    await expect(getTeacherTrialLessons({ date: '2026-06-13' })).resolves.toEqual({
      list: [{ id: 401, lead_id: 101, student_name: 'Student Wang', start_time: '2026-06-13 09:00:00', status: 'scheduled' }],
    })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { date: '2026-06-13' },
      header: { Authorization: 'Bearer teacher-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'GET',
      url: '/mobile/education/admissions/teacher/trial-lessons',
    }))
  })

  it('keeps_unassigned_feedback_as_forbidden_state', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 403, message: 'trial lesson is not assigned to current teacher', data: { trial_lesson_id: 401 } } })
    })

    let thrown: MobileApiError | undefined
    try {
      await submitTeacherTrialFeedback({ trial_lesson_id: 401, score: 4, content: 'Good focus' })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(403)
    expect(thrown?.data).toEqual({ trial_lesson_id: 401 })
  })

  it('teacher_pages_are_registered_and_do_not_accept_teacher_id_override', () => {
    const listSource = readFileSync(join(process.cwd(), 'src/pages/teacher/admissions/trial-lessons.vue'), 'utf8')
    const feedbackSource = readFileSync(join(process.cwd(), 'src/pages/teacher/admissions/trial-feedback.vue'), 'utf8')
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const paths = pagesJson.pages.map((page: { path: string }) => page.path)

    expect(paths).toEqual(expect.arrayContaining([
      'pages/teacher/admissions/trial-lessons',
      'pages/teacher/admissions/trial-feedback',
    ]))
    expect(listSource).toContain('getTeacherTrialLessons({ date: state.date })')
    expect(feedbackSource).toContain("state.status = isForbidden(error) ? 'forbidden' : 'error'")
    expect(listSource).not.toContain('teacher_id')
    expect(feedbackSource).not.toContain('teacher_id')
  })
})
