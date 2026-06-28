import { describe, expect, it } from 'vitest'
import { readdirSync, readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'

const contentDir = dirname(dirname(fileURLToPath(import.meta.url)))

describe('education content permission guards', () => {
  it('imports MineAdmin hasAuth helper in every page that uses permission guards', () => {
    const files = readdirSync(contentDir)
      .filter(file => file.endsWith('.vue'))

    for (const file of files) {
      const source = readFileSync(join(contentDir, file), 'utf8')
      if (!source.includes('hasAuth(')) {
        continue
      }

      expect(source, file).toContain('import hasAuth from \'@/utils/permission/hasAuth.ts\'')
    }
  })
})
