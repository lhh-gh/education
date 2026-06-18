import { describe, expect, it } from 'vitest'
import { readdirSync, readFileSync, statSync } from 'node:fs'
import { dirname, extname, join, relative } from 'node:path'
import { fileURLToPath } from 'node:url'

const viewsRoot = dirname(dirname(fileURLToPath(import.meta.url)))
const messageHookImport = '@/hooks/useMessage.ts'

function collectSourceFiles(directory: string): string[] {
  return readdirSync(directory).flatMap((entry) => {
    const path = join(directory, entry)
    const stat = statSync(path)

    if (stat.isDirectory()) {
      return collectSourceFiles(path)
    }

    if (path.endsWith('.spec.ts')) {
      return []
    }

    return ['.vue', '.ts'].includes(extname(path)) ? [path] : []
  })
}

describe('education view MineAdmin hook imports', () => {
  it('imports useMessage where MineAdmin message hook is used', () => {
    const offenders = collectSourceFiles(viewsRoot)
      .filter((path) => {
        const source = readFileSync(path, 'utf8')

        return source.includes('useMessage()') && !source.includes(messageHookImport)
      })
      .map(path => relative(viewsRoot, path))

    expect(offenders).toEqual([])
  })
})
