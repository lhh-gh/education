import type { EducationStatus } from '../../api/foundation/tenant.ts'

export function hasPermission(permissions: string[], code: string): boolean {
  return permissions.includes('*') || permissions.includes(code)
}

export function tenantActionsByPermission(permissions: string[], status: EducationStatus) {
  return {
    canCreate: hasPermission(permissions, 'education:foundation:tenant:create'),
    canEdit: hasPermission(permissions, 'education:foundation:tenant:update'),
    statusAction: hasPermission(permissions, 'education:foundation:tenant:status')
      ? status === 'enabled' ? 'disable' : 'enable'
      : null,
    canDelete: hasPermission(permissions, 'education:foundation:tenant:delete'),
  }
}

export function campusActionsByPermission(permissions: string[], status: EducationStatus, tenantId?: number) {
  return {
    canCreate: Boolean(tenantId) && hasPermission(permissions, 'education:foundation:campus:create'),
    canEdit: hasPermission(permissions, 'education:foundation:campus:update'),
    statusAction: hasPermission(permissions, 'education:foundation:campus:status')
      ? status === 'enabled' ? 'disable' : 'enable'
      : null,
    canDelete: hasPermission(permissions, 'education:foundation:campus:delete'),
  }
}

export function tenantRequired(tenantId?: number): boolean {
  return !tenantId || tenantId <= 0
}
