import type { MobileFoundationContext } from '../../../api/foundation/types'
import { operatorPageOptions } from '../../foundation/pageOptions'
import { createFoundationContextPage } from '../../foundation/useFoundationContextPage'

describe('operator entry page state', () => {
  beforeEach(() => {
    ;(global as any).uni = { stopPullDownRefresh: jest.fn() }
  })

  afterEach(() => {
    delete (global as any).uni
  })

  it('renders_operator_role_and_feature_flags', async () => {
    const page = createFoundationContextPage(() => Promise.resolve(operatorContext()), operatorPageOptions)

    await page.load()

    expect(page.state.status).toBe('success')
    expect(page.state.context?.profile.role_code).toBe('front_desk')
    expect(page.currentCampusName.value).toBe('East Campus')
    expect(page.enabledFeatureCodes.value).toEqual([
      'education.v1.core_academic',
      'education.v3.admissions_crm',
    ])
  })

  it('renders_error_and_retry', async () => {
    const loader = jest.fn()
      .mockRejectedValueOnce(new Error('Server unavailable'))
      .mockResolvedValueOnce(operatorContext())
    const page = createFoundationContextPage(loader, operatorPageOptions)

    await page.load()
    expect(page.state.status).toBe('error')
    expect(page.state.message).toBe('Server unavailable')

    await page.retry()
    expect(loader).toHaveBeenCalledTimes(2)
    expect(page.state.status).toBe('success')
  })
})

function operatorContext(overrides: Partial<MobileFoundationContext> = {}): MobileFoundationContext {
  return {
    tenant: {
      id: 1001,
      name: 'Demo Education',
      short_name: 'Demo',
    },
    profile: {
      id: 3003,
      user_id: 503,
      role_code: 'front_desk',
      display_name: 'Front Desk Chen',
      mobile: '13700000000',
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
      'education.v3.admissions_crm': true,
      'education.v7.family_service': false,
    },
    entry: {
      default_path: '/pages/operator/index',
      tabs: [],
    },
    empty_state: null,
    ...overrides,
  }
}
