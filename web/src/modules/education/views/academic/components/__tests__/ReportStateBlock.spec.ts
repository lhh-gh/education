import { describe, expect, it } from 'vitest'
import { reportStateMessage, shouldEmitRetry } from '../../reportRules.ts'

describe('report state block', () => {
  it('retry_emits_retry_event', () => {
    expect(shouldEmitRetry('error')).toBe(true)
    expect(shouldEmitRetry('forbidden')).toBe(true)
    expect(shouldEmitRetry('loading')).toBe(false)
  })

  it('uses_default_and_custom_messages', () => {
    expect(reportStateMessage('empty')).toBe('暂无报表数据')
    expect(reportStateMessage('error', 'Backend failed')).toBe('Backend failed')
  })
})
