import { readFileSync } from 'fs'
import { join } from 'path'

describe('guardian notice list page', () => {
  it('notice_list_defaults_to_unread', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/notice/index.vue'), 'utf8')

    expect(source).toContain("filter: 'unread'")
    expect(source).toContain('pageGuardianNotices')
    expect(source).toContain('onReachBottom(loadNextPage)')
    expect(source).toContain('/pages/guardian/notice/detail?receiptId=')
    expect(source).toContain('NoticeStatusBadge')
  })
})
