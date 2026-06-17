import { readFileSync } from 'fs'
import { join } from 'path'

describe('guardian pages.json routes', () => {
  it('guardian_academic_pages_are_registered', () => {
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string, style?: { navigationBarTitleText?: string } }>
    const titles = Object.fromEntries(pages.map((page) => [page.path, page.style?.navigationBarTitleText]))

    expect(titles['pages/guardian/student/index']).toBe('学生')
    expect(titles['pages/guardian/schedule/index']).toBe('课表')
    expect(titles['pages/guardian/account/index']).toBe('课时账户')
    expect(titles['pages/guardian/consumption/index']).toBe('课消记录')
    expect(titles['pages/guardian/notice/index']).toBe('通知')
    expect(titles['pages/guardian/notice/detail']).toBe('通知详情')
    expect(titles['pages/guardian/leave/create']).toBe('请假')
  })
})
