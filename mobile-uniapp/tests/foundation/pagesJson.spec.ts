import { readFileSync } from 'fs'
import { join } from 'path'

describe('foundation pages.json', () => {
  it('registers_teacher_guardian_operator_pages', () => {
    const pagesJson = JSON.parse(
      readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8')
    ) as { pages: Array<{ path: string; style?: { navigationBarTitleText?: string } }> }

    expect(pagesJson.pages).toEqual(expect.arrayContaining([
      expect.objectContaining({
        path: 'pages/teacher/index',
        style: expect.objectContaining({ navigationBarTitleText: 'Teacher' }),
      }),
      expect.objectContaining({
        path: 'pages/guardian/index',
        style: expect.objectContaining({ navigationBarTitleText: 'Guardian' }),
      }),
      expect.objectContaining({
        path: 'pages/operator/index',
        style: expect.objectContaining({ navigationBarTitleText: 'Operator' }),
      }),
    ]))
  })
})
