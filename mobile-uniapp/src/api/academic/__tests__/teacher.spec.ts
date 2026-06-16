import {
  approveTeacherLeaveRequest,
  getTeacherTodayLessons,
  MobileApiError,
  submitTeacherAttendance,
} from '../teacher'

describe('teacher academic api', () => {
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

  it('successful_envelope_unwraps_to_data', async () => {
    const data = { date: '2026-06-12', list: [{ id: 1, title: 'Drawing', status: 'scheduled' }] }
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data } })
    })

    await expect(getTeacherTodayLessons({ campus_id: 2001, date: '2026-06-12' })).resolves.toEqual(data)
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { campus_id: 2001, date: '2026-06-12' },
      method: 'GET',
      url: '/mobile/education/academic/teacher/lessons/today',
    }))
  })

  it('validation_failure_throws_mobile_api_error_with_field', async () => {
    requestMock.mockImplementation((options) => {
      options.success({
        data: {
          code: 422,
          message: 'records is required',
          data: { field: 'records' },
        },
      })
    })

    let thrown: MobileApiError | undefined
    try {
      await submitTeacherAttendance(12, { records: [] })
    } catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.message).toBe('records is required')
    expect(thrown?.code).toBe(422)
    expect(thrown?.data).toEqual({ field: 'records' })
  })

  it('business_failure_throws_mobile_api_error_with_code_and_message', async () => {
    requestMock.mockImplementation((options) => {
      options.success({
        data: {
          code: 409,
          message: 'only pending leave can be approved',
          data: { status: 'approved' },
        },
      })
    })

    let thrown: MobileApiError | undefined
    try {
      await approveTeacherLeaveRequest(99, { review_remark: 'ok' })
    } catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(409)
    expect(thrown?.message).toBe('only pending leave can be approved')
    expect(thrown?.data).toEqual({ status: 'approved' })
  })
})
