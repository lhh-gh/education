import { readFileSync } from 'fs'
import { join } from 'path'

describe('teacher academic pages.json', () => {
  it('registers_teacher_mobile_routes', () => {
    const pagesJson = JSON.parse(
      readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8')
    ) as { pages: Array<{ path: string; style?: { navigationBarTitleText?: string } }> }

    expect(pagesJson.pages).toEqual(expect.arrayContaining([
      route('pages/teacher/schedule/index', '今日课表'),
      route('pages/teacher/lesson/detail', '课节详情'),
      route('pages/teacher/lesson/attendance', '点名'),
      route('pages/teacher/leave/index', '请假审核'),
      route('pages/teacher/leave/detail', '请假详情'),
    ]))
  })
})

function route(path: string, title: string): object {
  return expect.objectContaining({
    path,
    style: expect.objectContaining({ navigationBarTitleText: title }),
  })
}
