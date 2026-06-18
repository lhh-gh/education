import { describe, expect, it } from 'vitest'
import {
  auditLogActionsByPermission,
  auditLogForbiddenWriteActions,
  defaultAuditLogSearch,
  normalizeAuditLogSearch,
  resetAuditLogSearch,
} from '../auditLogRules.ts'

describe('audit log list', () => {
  it('loads_audit_logs_with_default_foundation_filter', () => {
    expect(defaultAuditLogSearch()).toEqual({
      page: 1,
      pageSize: 20,
      module: 'foundation',
      keyword: '',
    })

    expect(normalizeAuditLogSearch(defaultAuditLogSearch())).toEqual({
      page: 1,
      pageSize: 20,
      module: 'foundation',
    })
  })

  it('search_maps_filters_to_api_params', () => {
    const params = normalizeAuditLogSearch({
      page: 2,
      pageSize: 50,
      module: 'foundation',
      action: 'education.foundation.campus.updated',
      business_id: '2001',
      actor_type: 'admin',
      keyword: '',
    }, ['2026-06-10 00:00:00', '2026-06-10 23:59:59'])

    expect(params).toMatchObject({
      page: 2,
      pageSize: 50,
      module: 'foundation',
      action: 'education.foundation.campus.updated',
      business_id: '2001',
      actor_type: 'admin',
      start_at: '2026-06-10 00:00:00',
      end_at: '2026-06-10 23:59:59',
    })
    expect(params.keyword).toBeUndefined()
  })

  it('reset_clears_filters_and_reloads', () => {
    const reset = resetAuditLogSearch()

    expect(reset.search).toEqual(defaultAuditLogSearch())
    expect(reset.dateRange).toEqual([])
  })

  it('detail_button_hidden_without_permission', () => {
    expect(auditLogActionsByPermission([]).canDetail).toBe(false)
    expect(auditLogActionsByPermission(['education:foundation:audit-log:detail']).canDetail).toBe(true)
  })

  it('does_not_render_write_buttons', () => {
    const actions = auditLogActionsByPermission(['*'])

    expect(actions.writeActions).toEqual([])
    expect(auditLogForbiddenWriteActions).toEqual(['create', 'edit', 'status', 'delete', 'import', 'export'])
  })
})
