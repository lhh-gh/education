import type { AcademicRecordStatus, Gender, GuardianRelation, StudentGuardianPayload } from '../../api/academic/profile.ts'

export interface AcademicActionState {
  canCreate: boolean
  canEdit: boolean
  canStatus: boolean
  canDelete: boolean
  statusAction: 'enable' | 'disable'
}

export function hasPermission(codes: string[], code: string): boolean {
  return codes.includes(code) || codes.includes('education:*')
}

export function recordActionsByPermission(codes: string[], resource: string, status: AcademicRecordStatus): AcademicActionState {
  return {
    canCreate: hasPermission(codes, `education:academic:${resource}:create`),
    canEdit: hasPermission(codes, `education:academic:${resource}:update`),
    canStatus: hasPermission(codes, `education:academic:${resource}:status`),
    canDelete: hasPermission(codes, `education:academic:${resource}:delete`),
    statusAction: status === 'enabled' ? 'disable' : 'enable',
  }
}

export function studentGuardianAction(codes: string[]): boolean {
  return hasPermission(codes, 'education:academic:student-guardian:save')
}

export function normalizePrimaryRelations(relations: StudentGuardianPayload[]): StudentGuardianPayload[] {
  const normalized = relations.map(item => ({ ...item, is_primary: false }))
  if (normalized.length === 0) {
    return normalized
  }
  const primaryIndex = relations.findIndex(item => item.is_primary)
  normalized[primaryIndex >= 0 ? primaryIndex : 0].is_primary = true
  return normalized
}

export function relationLabel(relation: GuardianRelation): string {
  return {
    father: '父亲',
    mother: '母亲',
    grandfather: '祖父/外祖父',
    grandmother: '祖母/外祖母',
    guardian: '监护人',
    other: '其他',
  }[relation]
}

export function academicStatusLabel(status?: AcademicRecordStatus | string | null): string {
  if (status === 'enabled') {
    return '启用'
  }
  if (status === 'disabled') {
    return '停用'
  }

  return '未知'
}

export function academicStatusTagType(status?: AcademicRecordStatus | string | null): 'success' | 'info' {
  return status === 'enabled' ? 'success' : 'info'
}

export function academicGenderLabel(gender?: Gender | string | null): string {
  if (gender === 'male') {
    return '男'
  }
  if (gender === 'female') {
    return '女'
  }

  return '未知'
}

export function academicActionText(status: AcademicRecordStatus): string {
  return status === 'enabled' ? '停用' : '启用'
}

export function classroomQuery(search: Record<string, unknown>): Record<string, unknown> {
  return {
    page: search.page,
    page_size: search.page_size,
    tenant_id: search.tenant_id,
    campus_id: search.campus_id,
    keyword: search.keyword,
    status: search.status,
  }
}

export function teacherProfileSelectorParams(tenantId?: number): { tenant_id?: number, role_code: 'teacher', status: 'enabled' } {
  return { tenant_id: tenantId, role_code: 'teacher', status: 'enabled' }
}
