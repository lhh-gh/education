import {
  getGuardianContext,
  getOperatorContext,
  getTeacherContext,
  MobileApiError,
} from '../context'
import type { MobileFoundationContext } from '../types'

describe('foundation context api', () => {
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

  it('getTeacherContext_unwraps_success_result', async () => {
    const data = foundationContext('teacher')
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data } })
    })

    await expect(getTeacherContext()).resolves.toEqual(data)
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      method: 'GET',
      url: '/mobile/education/foundation/teacher/context',
    }))
  })

  it('getGuardianContext_throws_api_message_on_403', async () => {
    requestMock.mockImplementation((options) => {
      options.success({
        data: {
          code: 403,
          message: 'guardian profile is not bound',
          data: { required_action: 'contact_campus' },
        },
      })
    })

    let thrown: MobileApiError | undefined
    try {
      await getGuardianContext()
    } catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.message).toBe('guardian profile is not bound')
    expect(thrown?.code).toBe(403)
    expect(thrown?.data).toEqual({ required_action: 'contact_campus' })
  })

  it('getOperatorContext_sends_campus_id_and_client_type', async () => {
    storageMock.mockImplementation((key) => (key === 'access_token' ? 'mobile-token' : ''))
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: foundationContext('front_desk') } })
    })

    await getOperatorContext({ campus_id: 2001, client_type: 'h5' })

    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { campus_id: 2001, client_type: 'h5' },
      header: { Authorization: 'Bearer mobile-token' },
      url: '/mobile/education/foundation/operator/context',
    }))
  })
})

function foundationContext(roleCode: MobileFoundationContext['profile']['role_code']): MobileFoundationContext {
  return {
    tenant: {
      id: 1001,
      name: 'Demo Education',
      short_name: 'Demo',
    },
    profile: {
      id: 3001,
      user_id: 501,
      role_code: roleCode,
      display_name: 'Mobile User',
      mobile: null,
      avatar: null,
      current_campus_id: 2001,
    },
    campus_scopes: [
      {
        campus_id: 2001,
        campus_name: 'East Campus',
        is_current: true,
      },
    ],
    feature_flags: {
      'education.v1.core_academic': true,
    },
    entry: {
      default_path: '/pages/teacher/index',
      tabs: [],
    },
    empty_state: null,
  }
}
