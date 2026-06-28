import type { StandardVersionRow } from '../../api/standards/types.ts'

export function versionActionState(row: Pick<StandardVersionRow, 'status' | 'review_status'>): { canPublish: boolean, badge: string } {
  if (row.status === 'published') {
    return { canPublish: false, badge: '已发布' }
  }

  return { canPublish: row.review_status === 'approved', badge: row.review_status === 'approved' ? '已通过' : '待评审' }
}

export function standardStatusLabel(status?: string): string {
  const labels: Record<string, string> = {
    draft: '草稿',
    reviewing: '评审中',
    published: '已发布',
    withdrawn: '已撤回',
    archived: '已归档',
    approved: '已通过',
    rejected: '已驳回',
    pending: '待处理',
  }

  return status ? labels[status] ?? status : '-'
}

export function stageGoalPayload(input: { tenant_id?: number, campus_id?: number, service_package_id: number, goal_code: string, goal_name: string, goal_content: string, ability_point_ids: number[] }) {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    service_package_id: input.service_package_id,
    goal_code: input.goal_code,
    goal_name: input.goal_name,
    goal_content: input.goal_content,
    ability_point_ids: [...input.ability_point_ids],
  }
}

export function qualityFilterPayload(input: { tenant_id?: number, campus_id?: number, course_id?: number, start_date?: string, end_date?: string }) {
  return {
    tenant_id: input.tenant_id,
    campus_id: input.campus_id,
    course_id: input.course_id,
    start_date: input.start_date,
    end_date: input.end_date,
  }
}
