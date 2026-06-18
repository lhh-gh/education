import type { MinePage, MineResult, PageParams } from '../foundation/types.ts'

export type NoticeTargetType = 'all' | 'campus' | 'class' | 'student'
export type NoticeType = 'academic' | 'activity' | 'fee' | 'system'
export type NoticePriority = 'normal' | 'important' | 'urgent'
export type NoticeStatus = 'draft' | 'published' | 'withdrawn'
export type NoticeReceiptStatus = 'unread' | 'read'

export interface NoticePageParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  campus_id?: number
  notice_type?: NoticeType
  target_type?: NoticeTargetType
  status?: NoticeStatus
  keyword?: string
}

export interface NoticeRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  notice_no: string
  notice_type: NoticeType
  target_type: NoticeTargetType
  target_id?: number | null
  title: string
  content: string
  priority: NoticePriority
  status: NoticeStatus
  published_at?: string | null
  published_by?: number | null
  withdrawn_at?: string | null
  withdrawn_by?: number | null
  withdraw_reason?: string | null
  expire_at?: string | null
  receipt_count: number
  read_count: number
  remark?: string | null
  created_at?: string | null
  updated_at?: string | null
}

export interface NoticeSavePayload {
  tenant_id?: number
  campus_id?: number
  notice_type: NoticeType
  target_type: NoticeTargetType
  target_id?: number
  title: string
  content: string
  priority: NoticePriority
  expire_at?: string
  remark?: string
}

export interface NoticePublishPayload {
  published_at?: string
}

export interface NoticeWithdrawPayload {
  withdraw_reason: string
}

export interface NoticePublishResult {
  notice: NoticeRecord
  receipt_count: number
}

export interface NoticeReceiptPageParams extends Partial<PageParams> {
  page?: number
  pageSize?: number
  tenant_id?: number
  status?: NoticeReceiptStatus
  student_id?: number
  guardian_id?: number
  keyword?: string
}

export interface NoticeReceiptRecord {
  id: number
  tenant_id: number
  campus_id?: number | null
  notice_id: number
  guardian_id: number
  student_id: number
  relation?: string | null
  guardian_name_snapshot: string
  student_name_snapshot: string
  status: NoticeReceiptStatus
  delivered_at?: string | null
  read_at?: string | null
  read_by_profile_id?: number | null
}

function tenantHeaders(tenantId?: number): { headers: Record<string, string> } | undefined {
  return tenantId && tenantId > 0 ? { headers: { 'X-Tenant-Id': String(tenantId) } } : undefined
}

function tenantIdFrom(paramsOrPayload: { tenant_id?: number }): number | undefined {
  return paramsOrPayload.tenant_id
}

export function pageNotices(params: NoticePageParams): Promise<MineResult<MinePage<NoticeRecord>>> {
  return useHttp().get('/admin/education/academic/notices/page', { params, ...tenantHeaders(tenantIdFrom(params)) })
}

export function getNotice(id: number, tenantId?: number): Promise<MineResult<NoticeRecord>> {
  return useHttp().get(`/admin/education/academic/notices/${id}`, tenantHeaders(tenantId))
}

export function createNotice(payload: NoticeSavePayload): Promise<MineResult<NoticeRecord>> {
  return useHttp().post('/admin/education/academic/notices', payload, tenantHeaders(tenantIdFrom(payload)))
}

export function updateNotice(id: number, payload: NoticeSavePayload): Promise<MineResult<NoticeRecord>> {
  return useHttp().put(`/admin/education/academic/notices/${id}`, payload, tenantHeaders(tenantIdFrom(payload)))
}

export function publishNotice(id: number, payload: NoticePublishPayload & { tenant_id?: number } = {}): Promise<MineResult<NoticePublishResult>> {
  return useHttp().put(`/admin/education/academic/notices/${id}/publish`, payload, tenantHeaders(tenantIdFrom(payload)))
}

export function withdrawNotice(id: number, payload: NoticeWithdrawPayload & { tenant_id?: number }): Promise<MineResult<NoticeRecord>> {
  return useHttp().put(`/admin/education/academic/notices/${id}/withdraw`, payload, tenantHeaders(tenantIdFrom(payload)))
}

export function pageNoticeReceipts(id: number, params: NoticeReceiptPageParams): Promise<MineResult<MinePage<NoticeReceiptRecord>>> {
  return useHttp().get(`/admin/education/academic/notices/${id}/receipts/page`, { params, ...tenantHeaders(tenantIdFrom(params)) })
}
