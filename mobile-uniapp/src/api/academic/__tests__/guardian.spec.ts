import {
  createGuardianLeave,
  getGuardianStudents,
  MobileApiError,
  pageGuardianStudentLessons,
} from '../guardian'

describe('guardian academic api', () => {
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
    const data = {
      list: [{ id: 101, student_no: 'S001', name: 'Student Zhang', relation: 'mother' }],
      total: 1,
      page: 1,
      pageSize: 20,
    }
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data } })
    })

    await expect(getGuardianStudents({ client_type: 'h5' })).resolves.toEqual(data)
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { client_type: 'h5' },
      method: 'GET',
      url: '/mobile/education/academic/guardian/students',
    }))
  })

  it('validation_failure_preserves_data_field', async () => {
    requestMock.mockImplementation((options) => {
      options.success({
        data: {
          code: 422,
          message: 'reason is required',
          data: { field: 'reason' },
        },
      })
    })

    let thrown: MobileApiError | undefined
    try {
      await createGuardianLeave({ lesson_student_id: 10001, leave_type: 'sick', reason: '' })
    } catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.message).toBe('reason is required')
    expect(thrown?.code).toBe(422)
    expect(thrown?.data).toEqual({ field: 'reason' })
  })

  it('business_failure_preserves_code_and_message', async () => {
    requestMock.mockImplementation((options) => {
      options.success({
        data: {
          code: 403,
          message: 'student is not bound to current guardian',
          data: { student_id: 999 },
        },
      })
    })

    let thrown: MobileApiError | undefined
    try {
      await pageGuardianStudentLessons(999, {
        start_at: '2026-06-01 00:00:00',
        end_at: '2026-06-30 23:59:59',
      })
    } catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(403)
    expect(thrown?.message).toBe('student is not bound to current guardian')
    expect(thrown?.data).toEqual({ student_id: 999 })
  })
})
