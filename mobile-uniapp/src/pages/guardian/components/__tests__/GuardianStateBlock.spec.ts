import { readFileSync } from 'fs'
import { join } from 'path'

describe('GuardianStateBlock component', () => {
  it('retry_emits_retry_event', () => {
    const source = readFileSync(join(process.cwd(), 'src/pages/guardian/components/GuardianStateBlock.vue'), 'utf8')

    expect(source).toContain("(event: 'retry'): void")
    expect(source).toContain("@tap=\"$emit('retry')\"")
    expect(source).toContain("'loading' | 'empty' | 'error' | 'forbidden' | 'not_found' | 'conflict'")
  })
})
