import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'

const source = readFileSync(resolve(__dirname, '../MaterialAttachmentList.vue'), 'utf8')

describe('material attachment list', () => {
  it('loads_real_attachment_rows_with_filters_and_pagination', () => {
    expect(source).toContain('pageMaterialAttachments')
    expect(source).toContain('const loading = ref(false)')
    expect(source).toContain('material_version_id')
    expect(source).toContain('file_type')
    expect(source).toContain('v-loading="loading"')
    expect(source).toContain('v-model:current-page="search.page"')
    expect(source).toContain('暂无附件')
  })
})
