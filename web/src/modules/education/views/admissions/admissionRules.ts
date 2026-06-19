export const admissionPermissionCodes = {
  createLead: 'education:admissions:lead:create',
  assignLead: 'education:admissions:lead:assign',
  followLead: 'education:admissions:lead:follow',
  convertLead: 'education:admissions:lead:convert',
  createTrial: 'education:admissions:trial:create',
  attendance: 'education:admissions:trial:attendance',
  feedback: 'education:admissions:trial-feedback:create',
}

const stageLabels: Record<string, string> = {
  assigned: '已分配',
  converted: '已转化',
  followed: '已跟进',
  lost: '已流失',
  new: '新线索',
  trial_done: '已试听',
  trial_scheduled: '已预约试听',
}

const statusLabels: Record<string, string> = {
  active: '跟进中',
  attended: '已到课',
  cancelled: '已取消',
  converted: '已转化',
  done: '已完成',
  enabled: '启用',
  disabled: '停用',
  invalid: '无效',
  lost: '已流失',
  pending: '待处理',
  scheduled: '已预约',
}

export function admissionPermissions(has: (code: string) => boolean) {
  return {
    createLead: has(admissionPermissionCodes.createLead),
    assignLead: has(admissionPermissionCodes.assignLead),
    followLead: has(admissionPermissionCodes.followLead),
    convertLead: has(admissionPermissionCodes.convertLead),
    createTrial: has(admissionPermissionCodes.createTrial),
    attendance: has(admissionPermissionCodes.attendance),
    feedback: has(admissionPermissionCodes.feedback),
  }
}

export function admissionStageLabel(status?: string): string {
  return status ? (stageLabels[status] ?? status) : ''
}

export function admissionStatusLabel(status?: string): string {
  return status ? (statusLabels[status] ?? admissionStageLabel(status)) : ''
}

export function admissionTagType(status?: string): 'success' | 'warning' | 'info' | 'danger' {
  if (status === 'converted' || status === 'done' || status === 'attended') {
    return 'success'
  }
  if (status === 'lost' || status === 'invalid' || status === 'cancelled') {
    return 'danger'
  }
  if (status === 'pending' || status === 'scheduled') {
    return 'warning'
  }

  return 'info'
}

export function admissionErrorText(error: any): string {
  if (error?.code === 409 && error?.data?.lead_id) {
    return `线索已存在：#${error.data.lead_id}`
  }
  if (error?.code === 409 && error?.data?.conflict_lesson_id) {
    return `试听时间冲突：#${error.data.conflict_lesson_id}`
  }
  if (error?.message === 'Permission denied') {
    return '暂无操作权限'
  }

  return error?.message ?? '招生操作失败'
}
