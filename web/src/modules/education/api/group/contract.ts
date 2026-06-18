import type { MineResult } from '../foundation/types.ts'
import type { GroupPage, GroupScopedParams } from './types.ts'
import { groupGetOptions, groupRequestOptions } from './types.ts'

export interface ContractRecord {
  id: number
  contract_no: string
  contract_type: string
  title: string
  counterparty_name: string
  amount_cents: number
  status: string
  risk_level?: string
}

export interface ContractPayload extends GroupScopedParams {
  id?: number
  contract_no: string
  contract_type: string
  title: string
  counterparty_name: string
  amount_cents: number
  start_date?: string
  end_date?: string
  status?: string
  risk_level?: string
}

export interface ContractAttachmentPayload extends GroupScopedParams {
  file_name: string
  file_url: string
}

export interface ContractRenewalRecord {
  id: number
  contract_id: number
  renewal_type: string
  status: string
  due_date: string
}

export function pageContracts(params: GroupScopedParams): Promise<MineResult<GroupPage<ContractRecord>>> {
  return useHttp().get('/admin/education/group/contracts/page', groupGetOptions(params))
}

export function saveContract(payload: ContractPayload): Promise<MineResult<{ contract_id: number, status: string }>> {
  return useHttp().post('/admin/education/group/contracts', payload, groupRequestOptions(payload))
}

export function submitContractReview(id: number, params: GroupScopedParams = {}): Promise<MineResult<{ contract_id: number, status: string }>> {
  return useHttp().post(`/admin/education/group/contracts/${id}/submit-review`, {}, groupRequestOptions(params))
}

export function uploadContractAttachment(id: number, payload: ContractAttachmentPayload): Promise<MineResult<Record<string, unknown>>> {
  return useHttp().post(`/admin/education/group/contracts/${id}/attachments`, payload, groupRequestOptions(payload))
}

export function pageContractRenewals(params: GroupScopedParams): Promise<MineResult<GroupPage<ContractRenewalRecord>>> {
  return useHttp().get('/admin/education/group/contract-renewals/page', groupGetOptions(params))
}
