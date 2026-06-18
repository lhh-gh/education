import { existsSync, readFileSync, readdirSync, statSync } from 'fs'
import { join, relative } from 'path'

function collectSourceFiles(root: string): string[] {
  if (!existsSync(root)) {
    return []
  }

  return readdirSync(root).flatMap((name) => {
    const path = join(root, name)
    const stat = statSync(path)

    if (stat.isDirectory()) {
      return collectSourceFiles(path)
    }

    return /\.(json|ts|vue)$/.test(path) ? [path] : []
  })
}

describe('course standards mobile visibility regression', () => {
  it('does_not_register_direct_guardian_or_teacher_standard_pages_before_content_center_exists', () => {
    const pagesJson = JSON.parse(readFileSync(join(process.cwd(), 'src/pages.json'), 'utf8'))
    const pages = pagesJson.pages as Array<{ path: string }>
    const standardPages = pages
      .map(page => page.path)
      .filter(path => /standard|standards|course-material|content-center/.test(path))

    expect(standardPages).toEqual([])
  })

  it('family_and_future_content_surfaces_do_not_call_standards_endpoints_directly', () => {
    const scopedFiles = collectSourceFiles(join(process.cwd(), 'src'))
      .filter((path) => {
        const normalized = relative(process.cwd(), path).replace(/\\/g, '/')

        return normalized.includes('/family/') || normalized.includes('/content/')
      })
    const forbiddenMatches = scopedFiles.flatMap((path) => {
      const source = readFileSync(path, 'utf8')
      const normalized = relative(process.cwd(), path).replace(/\\/g, '/')

      return [
        '/mobile/education/standards',
        '/admin/education/standards',
        'standard_version_id',
      ]
        .filter(token => source.includes(token))
        .map(token => `${normalized}:${token}`)
    })

    expect(forbiddenMatches).toEqual([])
  })

  it('guardian_family_pages_keep_published_only_indirect_records', () => {
    const pagePaths = [
      'src/pages/guardian/family/learning-reports.vue',
      'src/pages/guardian/family/lesson-comments.vue',
      'src/pages/guardian/family/growth-records.vue',
    ]

    for (const pagePath of pagePaths) {
      const source = readFileSync(join(process.cwd(), pagePath), 'utf8')

      expect(source).toContain("status === 'published'")
      expect(source).not.toContain("status !== 'withdrawn'")
    }
  })
})
