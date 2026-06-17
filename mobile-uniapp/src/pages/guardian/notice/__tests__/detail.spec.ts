import { readFileSync } from 'fs'
import { join } from 'path'

describe('guardian notice detail page', () => {
  it('notice_detail_marks_unread_as_read', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/notice/detail.vue'), 'utf8')

    expect(source).toContain('getGuardianNotice')
    expect(source).toContain("state.notice.status === 'unread'")
    expect(source).toContain('readGuardianNotice(state.receiptId)')
    expect(source).toContain("state.status = isNotFound(error) ? 'not_found'")
    expect(source).toContain('Read successfully')
  })
})
