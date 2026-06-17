export const admissionPermissionCodes = {
  createLead: 'education:admissions:lead:create',
  assignLead: 'education:admissions:lead:assign',
  followLead: 'education:admissions:lead:follow',
  convertLead: 'education:admissions:lead:convert',
  createTrial: 'education:admissions:trial:create',
  attendance: 'education:admissions:trial:attendance',
  feedback: 'education:admissions:trial-feedback:create',
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
    return `Duplicate lead #${error.data.lead_id}`
  }
  if (error?.code === 409 && error?.data?.conflict_lesson_id) {
    return `Trial conflict #${error.data.conflict_lesson_id}`
  }

  return error?.message ?? 'Admission operation failed'
}
