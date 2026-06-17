import { readFileSync } from 'fs'
import { join } from 'path'
import {
  getTeacherSalarySlips,
  getTeacherWorkloadDisputes,
  MobileApiError,
  submitTeacherWorkloadDispute,
} from '../../src/api/payroll/teacher'

describe('teacher payroll api and pages', () => {
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

  it('salary_slips_use_mobile_headers_and_backend_teacher_scope', async () => {
    storageMock.mockImplementation((key: string) => ({
      access_token: 'teacher-token',
      education_tenant_id: 11,
      education_campus_id: 22,
    })[key])
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { list: [], total: 0 } } })
    })

    await expect(getTeacherSalarySlips({ salary_month: '2026-06', status: 'approved' })).resolves.toEqual({ list: [], total: 0 })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { salary_month: '2026-06', status: 'approved' },
      header: { Authorization: 'Bearer teacher-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'GET',
      url: '/mobile/education/payroll/teacher/slips',
    }))
  })

  it('dispute_submission_preserves_validation_response', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 422, message: 'content is required', data: { field: 'content' } } })
    })

    let thrown: MobileApiError | undefined
    try {
      await submitTeacherWorkloadDispute({ source_workload_id: 501, dispute_type: 'lesson_count', content: '' })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(422)
    expect(thrown?.message).toBe('content is required')
    expect(thrown?.data).toEqual({ field: 'content' })
  })

  it('exposes_dispute_list_endpoint', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { list: [], total: 0 } } })
    })

    await getTeacherWorkloadDisputes({ status: 'pending' })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { status: 'pending' },
      url: '/mobile/education/payroll/teacher/disputes',
    }))
  })

  it('teacher_payroll_pages_are_registered_with_teacher_meta', () => {
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string, meta?: { role?: string, requiresProfile?: string } }>
    const payrollPages = pages.filter(page => page.path.startsWith('pages/teacher/payroll/'))

    expect(payrollPages.map(page => page.path)).toEqual(expect.arrayContaining([
      'pages/teacher/payroll/slips',
      'pages/teacher/payroll/slip-detail',
      'pages/teacher/payroll/disputes',
      'pages/teacher/payroll/dispute-form',
    ]))
    expect(payrollPages.every(page => page.meta?.role === 'teacher' && page.meta?.requiresProfile === 'teacher')).toBe(true)
  })

  it('teacher_payroll_pages_do_not_accept_teacher_id_override', () => {
    const slips = readFileSync(join(process.cwd(), 'src/pages/teacher/payroll/slips.vue'), 'utf8')
    const disputes = readFileSync(join(process.cwd(), 'src/pages/teacher/payroll/disputes.vue'), 'utf8')
    const form = readFileSync(join(process.cwd(), 'src/pages/teacher/payroll/dispute-form.vue'), 'utf8')

    expect(slips).toContain('getTeacherSalarySlips({ salary_month: state.salaryMonth')
    expect(disputes).toContain('getTeacherWorkloadDisputes({ status: state.filter')
    expect(form).toContain('submitTeacherWorkloadDispute')
    expect(`${slips}\n${disputes}\n${form}`).not.toContain('teacher_id')
  })
})
