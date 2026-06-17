import type { RouteRecordRaw } from 'vue-router'
import { describe, expect, it, vi } from 'vitest'
import educationRoutes from '@/router/modules/education.ts'
import { canEditNotice, canPublishNotice, canWithdrawNotice, noticePriorityType, noticeStatusType } from '../noticeRules.ts'

function flattenRoutes(routes: RouteRecordRaw[]): RouteRecordRaw[] {
  return routes.flatMap(route => [route, ...flattenRoutes(route.children ?? [])])
}

function notice(status: 'draft' | 'published' | 'withdrawn') {
  return {
    id: 1,
    tenant_id: 1,
    notice_no: 'NOT001',
    notice_type: 'academic',
    target_type: 'class',
    title: 'Class reminder',
    content: 'Bring tools.',
    priority: 'important',
    status,
    receipt_count: 1,
    read_count: 0,
  } as const
}

describe('notice list', () => {
  it('renders_notice_route', () => {
    const route = flattenRoutes(educationRoutes).find(item => item.name === 'EducationAcademicNoticeList')

    expect(route?.path).toBe('/education/academic/notices')
    expect(route?.component).toEqual(expect.any(Function))
    expect(route?.meta?.auth).toEqual(['education:academic:notice:page'])
  })

  it('permission_buttons_hide_by_status_and_permission', () => {
    expect(canEditNotice(notice('draft'), true)).toBe(true)
    expect(canEditNotice(notice('published'), true)).toBe(false)
    expect(canPublishNotice(notice('draft'), false)).toBe(false)
    expect(canWithdrawNotice(notice('published'), true)).toBe(true)
    expect(canWithdrawNotice(notice('withdrawn'), true)).toBe(false)
  })

  it('publish_success_reloads_table', async () => {
    const loadRows = vi.fn()
    const publishNotice = vi.fn().mockResolvedValue({ data: { receipt_count: 1 } })

    await publishNotice(1, {})
    loadRows()

    expect(publishNotice).toHaveBeenCalledWith(1, {})
    expect(loadRows).toHaveBeenCalledTimes(1)
    expect(noticeStatusType('published')).toBe('success')
    expect(noticePriorityType('urgent')).toBe('danger')
  })
})
