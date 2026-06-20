import { describe, expect, it } from 'vitest'
import { readdirSync, readFileSync, statSync } from 'node:fs'
import { join } from 'node:path'

function collectVueFiles(dir: string): string[] {
  return readdirSync(dir).flatMap((name) => {
    const path = join(dir, name)
    if (statSync(path).isDirectory()) {
      return collectVueFiles(path)
    }

    return path.endsWith('.vue') ? [path] : []
  })
}

describe('education business pages shared scope', () => {
  it('does not render local tenant or campus query inputs outside foundation and form components', () => {
    const root = join(process.cwd(), 'src/modules/education/views')
    const offenders = collectVueFiles(root)
      .filter(path => !path.includes('/foundation/'))
      .filter(path => !path.includes('/components/') || path.endsWith('/ReportDateRangeFilter.vue'))
      .filter((path) => {
        const content = readFileSync(path, 'utf8')
        return /v-model="[^"]*\.(tenant_id|campus_id)"/.test(content)
      })

    expect(offenders).toEqual([])
  })
})
