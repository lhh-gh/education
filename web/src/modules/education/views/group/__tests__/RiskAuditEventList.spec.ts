import { describe, expect, it } from 'vitest'
import { riskAuditQueryParams } from '../groupRules.ts'

describe('risk audit event list', () => {
  it('risk_level_and_handled_filters_are_sent_to_api', () => {
    expect(riskAuditQueryParams({ risk_level: 'high', handled: true, page: 1, pageSize: 20 })).toEqual({
      risk_level: 'high',
      handled: true,
      page: 1,
      pageSize: 20,
    })
  })
})
