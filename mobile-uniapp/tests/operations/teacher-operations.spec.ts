import { readFileSync } from 'fs'
import { join } from 'path'
import {
  getChangedLessons,
  getWorkloadSummary,
  MobileApiError,
  submitMakeupAttendance,
} from '../../src/api/operations/teacher'

describe('teacher operations api and pages', () => {
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

  it('changed_lessons_use_mobile_headers_and_hide_unassigned_lessons_by_backend_scope', async () => {
    storageMock.mockImplementation((key: string) => ({ access_token: 'teacher-token', education_tenant_id: 11, education_campus_id: 22 })[key])
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { list: [], total: 0 } } })
    })

    await expect(getChangedLessons({ status: 'upcoming' })).resolves.toEqual({ list: [], total: 0 })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { status: 'upcoming' },
      header: { Authorization: 'Bearer teacher-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'GET',
      url: '/mobile/education/operations/teacher/changed-lessons',
    }))
  })

  it('submit_makeup_attendance_preserves_duplicate_used_response', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 409, message: 'makeup entitlement is used', data: { makeup_record_id: 501 } } })
    })

    let thrown: MobileApiError | undefined
    try {
      await submitMakeupAttendance({ makeup_record_id: 501, attendance_status: 'present' })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(409)
    expect(thrown?.data).toEqual({ makeup_record_id: 501 })
  })

  it('workload_summary_does_not_accept_teacher_id_override_in_page', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/teacher/operations/workload-summary.vue'), 'utf8')

    expect(source).toContain('getWorkloadSummary({ month: state.month })')
    expect(source).not.toContain('teacher_id')
    expect(getWorkloadSummary).toEqual(expect.any(Function))
  })

  it('teacher_operations_pages_are_registered', () => {
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const paths = pagesJson.pages.map((page: { path: string }) => page.path)

    expect(paths).toEqual(expect.arrayContaining([
      'pages/teacher/operations/changed-lessons',
      'pages/teacher/operations/makeup-attendance',
      'pages/teacher/operations/workload-summary',
    ]))
  })
})
