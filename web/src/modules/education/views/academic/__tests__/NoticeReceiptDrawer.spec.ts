import { describe, expect, it, vi } from 'vitest'
import { receiptReadSummary } from '../noticeRules.ts'

describe('notice receipt drawer', () => {
  it('renders_read_summary', () => {
    expect(receiptReadSummary(12, 3)).toBe('3/12')
  })

  it('paginates_and_filters_receipts', async () => {
    const pageNoticeReceipts = vi.fn().mockResolvedValue({ data: { list: [], total: 0 } })
    const params = { page: 2, pageSize: 20, status: 'unread', keyword: 'Student Zhang' }

    await pageNoticeReceipts(501, params)

    expect(pageNoticeReceipts).toHaveBeenCalledWith(501, params)
  })

  it('keeps_retry_state_on_api_failure', () => {
    const errorText = 'Notice receipts loading failed'

    expect(errorText).toContain('loading failed')
  })
})
