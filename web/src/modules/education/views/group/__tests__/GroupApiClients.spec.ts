import { afterEach, describe, expect, it, vi } from 'vitest'
import { completeApprovalTask, createApprovalInstance, pageApprovalTasks, pageApprovalTemplates, saveApprovalTemplate } from '../../../api/group/approval.ts'
import { pageContractRenewals, pageContracts, saveContract, submitContractReview, uploadContractAttachment } from '../../../api/group/contract.ts'
import { pageFranchiseRecords, saveFranchiseRecord } from '../../../api/group/franchise.ts'
import { getGroupOperationDashboard, getGroupOperationMetrics } from '../../../api/group/metric.ts'
import { bindCampusOrgRelation, getOrgUnitTree, saveOrgUnit } from '../../../api/group/org.ts'
import { getUserDataScopePreview, pageDataPermissions, saveUserDataPermission } from '../../../api/group/permission.ts'
import { markRiskAuditHandled, pageRiskAuditEvents } from '../../../api/group/risk-audit.ts'

interface HttpCall {
  method: 'GET' | 'POST'
  url: string
  data?: unknown
  config?: unknown
}

function stubHttp(): HttpCall[] {
  const calls: HttpCall[] = []
  const response = Promise.resolve({ code: 200, message: 'success', data: {} })

  vi.stubGlobal('useHttp', () => ({
    get: (url: string, config?: unknown) => {
      calls.push({ method: 'GET', url, config })
      return response
    },
    post: (url: string, data?: unknown, config?: unknown) => {
      calls.push({ method: 'POST', url, data, config })
      return response
    },
  }))

  return calls
}

afterEach(() => {
  vi.unstubAllGlobals()
})

describe('education group api clients', () => {
  it('uses_expected_group_endpoints', () => {
    const calls = stubHttp()

    getOrgUnitTree({ tenant_id: 1 })
    saveOrgUnit({ tenant_id: 1, code: 'HQ', name: 'Headquarters', unit_type: 'group' })
    bindCampusOrgRelation({ tenant_id: 1, org_unit_id: 10, campus_id: 20 })
    pageDataPermissions({ tenant_id: 1, page: 1, pageSize: 20 })
    saveUserDataPermission({ tenant_id: 1, user_id: 88, scope_type: 'campus_set', campus_ids: [20] })
    getUserDataScopePreview({ tenant_id: 1 })
    pageApprovalTemplates({ tenant_id: 1, page: 1, pageSize: 20 })
    saveApprovalTemplate({ tenant_id: 1, template_code: 'C', template_name: 'Contract', business_type: 'contract', nodes: [] })
    createApprovalInstance({ tenant_id: 1, template_id: 1, business_type: 'contract', business_id: 2 })
    pageApprovalTasks({ tenant_id: 1, page: 1, pageSize: 20 })
    completeApprovalTask(300, { tenant_id: 1, result: 'approved', comment: 'ok' })
    pageContracts({ tenant_id: 1, page: 1, pageSize: 20 })
    saveContract({ tenant_id: 1, contract_no: 'CT', contract_type: 'lease', title: 'Lease', counterparty_name: 'Landlord', amount_cents: 1 })
    submitContractReview(100, { tenant_id: 1 })
    uploadContractAttachment(100, { tenant_id: 1, file_name: 'a.pdf', file_url: '/a.pdf' })
    pageContractRenewals({ tenant_id: 1, page: 1, pageSize: 20 })
    getGroupOperationMetrics({ tenant_id: 1, start_date: '2026-06-01', end_date: '2026-06-30' })
    getGroupOperationDashboard({ tenant_id: 1 })
    pageFranchiseRecords({ tenant_id: 1, page: 1, pageSize: 20 })
    saveFranchiseRecord({ tenant_id: 1, franchise_code: 'F', franchise_name: 'Franchise' })
    pageRiskAuditEvents({ tenant_id: 1, risk_level: 'high' })
    markRiskAuditHandled(900, { tenant_id: 1 })

    expect(calls.map(call => [call.method, call.url])).toEqual([
      ['GET', '/admin/education/group/org-units/tree'],
      ['POST', '/admin/education/group/org-units'],
      ['POST', '/admin/education/group/campus-org-relations'],
      ['GET', '/admin/education/group/data-permissions/page'],
      ['POST', '/admin/education/group/data-permissions'],
      ['GET', '/admin/education/group/data-permissions/preview'],
      ['GET', '/admin/education/group/approval-templates/page'],
      ['POST', '/admin/education/group/approval-templates'],
      ['POST', '/admin/education/group/approval-instances'],
      ['GET', '/admin/education/group/approval-tasks/page'],
      ['POST', '/admin/education/group/approval-tasks/300/complete'],
      ['GET', '/admin/education/group/contracts/page'],
      ['POST', '/admin/education/group/contracts'],
      ['POST', '/admin/education/group/contracts/100/submit-review'],
      ['POST', '/admin/education/group/contracts/100/attachments'],
      ['GET', '/admin/education/group/contract-renewals/page'],
      ['GET', '/admin/education/group/operation-metrics'],
      ['GET', '/admin/education/group/operation-dashboard'],
      ['GET', '/admin/education/group/franchises/page'],
      ['POST', '/admin/education/group/franchises'],
      ['GET', '/admin/education/group/risk-audit-events/page'],
      ['POST', '/admin/education/group/risk-audit-events/900/handled'],
    ])
  })
})
