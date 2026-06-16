import { readFileSync } from 'fs'
import { join } from 'path'

describe('TeacherStateBlock component', () => {
  const source = readFileSync(join(process.cwd(), 'src/pages/teacher/components/TeacherStateBlock.vue'), 'utf8')

  it('supports_stable_page_states_and_retry_action', () => {
    expect(source).toContain("'loading' | 'empty' | 'error' | 'forbidden'")
    expect(source).toContain("@tap=\"$emit('retry')\"")
    expect(source).toContain('min-height: 420rpx')
    expect(source).toContain('border-radius: 8rpx')
  })
})
