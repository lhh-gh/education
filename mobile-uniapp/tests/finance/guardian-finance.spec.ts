import { readFileSync } from 'fs'
import { join } from 'path'
import {
  getGuardianFinanceOrders,
  getGuardianFinanceReceipts,
  getGuardianFinanceRefunds,
  MobileApiError,
} from '../../src/api/finance/guardian'

describe('guardian finance api and pages', () => {
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

  it('uses_selected_student_and_mobile_headers_for_orders', async () => {
    storageMock.mockImplementation((key: string) => ({
      access_token: 'guardian-token',
      education_tenant_id: 11,
      education_campus_id: 22,
      guardian_selected_student_id: 1201,
    })[key])
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { list: [], total: 0 } } })
    })

    await expect(getGuardianFinanceOrders({ status: 'pending' })).resolves.toEqual({ list: [], total: 0 })
    expect(requestMock).toHaveBeenCalledWith(expect.objectContaining({
      data: { status: 'pending', student_id: 1201 },
      header: { Authorization: 'Bearer guardian-token', 'X-Tenant-Id': '11', 'X-Campus-Id': '22' },
      method: 'GET',
      url: '/mobile/education/finance/guardian/orders',
    }))
  })

  it('preserves_unbound_student_forbidden_response', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 403, message: 'student is not bound to current guardian', data: { student_id: 999 } } })
    })

    let thrown: MobileApiError | undefined
    try {
      await getGuardianFinanceRefunds({ student_id: 999 })
    }
    catch (error) {
      thrown = error as MobileApiError
    }

    expect(thrown).toBeInstanceOf(MobileApiError)
    expect(thrown?.code).toBe(403)
    expect(thrown?.message).toBe('student is not bound to current guardian')
    expect(thrown?.data).toEqual({ student_id: 999 })
  })

  it('exposes_receipt_and_refund_finance_lists', async () => {
    requestMock.mockImplementation((options) => {
      options.success({ data: { code: 200, message: 'success', data: { list: [], total: 0 } } })
    })

    await getGuardianFinanceReceipts({ student_id: 1201, status: 'issued' })
    await getGuardianFinanceRefunds({ student_id: 1201, status: 'refunded' })

    expect(requestMock).toHaveBeenNthCalledWith(1, expect.objectContaining({
      data: { student_id: 1201, status: 'issued' },
      url: '/mobile/education/finance/guardian/receipts',
    }))
    expect(requestMock).toHaveBeenNthCalledWith(2, expect.objectContaining({
      data: { student_id: 1201, status: 'refunded' },
      url: '/mobile/education/finance/guardian/refunds',
    }))
  })

  it('guardian_finance_pages_are_registered_with_selected_student_meta', () => {
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string, meta?: { role?: string, requiresSelectedStudent?: boolean } }>
    const financePages = pages.filter(page => page.path.startsWith('pages/guardian/finance/'))

    expect(financePages.map(page => page.path)).toEqual(expect.arrayContaining([
      'pages/guardian/finance/orders',
      'pages/guardian/finance/order-detail',
      'pages/guardian/finance/receipts',
      'pages/guardian/finance/refunds',
    ]))
    expect(financePages.every(page => page.meta?.role === 'guardian' && page.meta?.requiresSelectedStudent === true)).toBe(true)
  })

  it('guardian_finance_pages_show_receipt_refund_and_unbound_states', () => {
    const orders = readFileSync(join(process.cwd(), 'src/pages/guardian/finance/orders.vue'), 'utf8')
    const detail = readFileSync(join(process.cwd(), 'src/pages/guardian/finance/order-detail.vue'), 'utf8')
    const refunds = readFileSync(join(process.cwd(), 'src/pages/guardian/finance/refunds.vue'), 'utf8')

    expect(orders).toContain('Receipt link')
    expect(detail).toContain('student is not bound')
    expect(refunds).toContain('refund-status')
  })
})
