import type { EducationStatus } from '../../api/foundation/tenant.ts'
import type { ConfigOwnerType, FoundationStatus } from '../../api/foundation/dictionary.ts'
import type { EducationRoleCode } from '../../api/foundation/userProfile.ts'

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

export function isPlatformRole(roleCode: EducationRoleCode): boolean {
  return roleCode === 'platform_super_admin' || roleCode === 'platform_operator'
}

export function userProfileActionsByPermission(permissions: string[], status: EducationStatus, roleCode: EducationRoleCode) {
  return {
    canCreate: hasPermission(permissions, 'education:foundation:user-profile:create'),
    canEdit: hasPermission(permissions, 'education:foundation:user-profile:update'),
    statusAction: hasPermission(permissions, 'education:foundation:user-profile:status')
      ? status === 'enabled' ? 'disable' : 'enable'
      : null,
    canCampusScope: !isPlatformRole(roleCode) && hasPermission(permissions, 'education:foundation:campus-scope:save'),
  }
}

export function campusScopeSavePayload(campusIds: number[]): number[] {
  return [...new Set(campusIds.map(Number))]
    .filter(campusId => campusId > 0)
    .sort((left, right) => left - right)
}

export function campusScopeValidationError(roleCode: EducationRoleCode, campusIds: number[]): string | null {
  const requiresScope = ['principal', 'academic_staff', 'front_desk', 'teacher', 'finance'].includes(roleCode)

  return requiresScope && campusScopeSavePayload(campusIds).length === 0
    ? 'campus scope is required'
    : null
}

export function dictionaryActionsByPermission(permissions: string[], isLocked: boolean, typeStatus?: FoundationStatus, itemStatus?: FoundationStatus) {
  return {
    canCreateType: hasPermission(permissions, 'education:foundation:dictionary:create'),
    canEditType: hasPermission(permissions, 'education:foundation:dictionary:update'),
    typeStatusAction: hasPermission(permissions, 'education:foundation:dictionary:status') && typeStatus
      ? typeStatus === 'enabled' ? 'disable' : 'enable'
      : null,
    canDeleteType: !isLocked && hasPermission(permissions, 'education:foundation:dictionary:delete'),
    canCreateItem: hasPermission(permissions, 'education:foundation:dictionary-item:create'),
    canEditItem: hasPermission(permissions, 'education:foundation:dictionary-item:update'),
    itemStatusAction: hasPermission(permissions, 'education:foundation:dictionary-item:status') && itemStatus
      ? itemStatus === 'enabled' ? 'disable' : 'enable'
      : null,
    canDeleteItem: !isLocked && hasPermission(permissions, 'education:foundation:dictionary-item:delete'),
  }
}

export function dictionaryOwnerTypeOptions(isPlatformContext: boolean): Array<{ label: string, value: ConfigOwnerType }> {
  const tenantOption = { label: '租户', value: 'tenant' as const }

  return isPlatformContext
    ? [{ label: '系统', value: 'system' as const }, tenantOption]
    : [tenantOption]
}

export function featureFlagActionsByPermission(permissions: string[], enabled: boolean, isLocked: boolean, status?: FoundationStatus) {
  return {
    canCreate: hasPermission(permissions, 'education:foundation:feature-flag:create'),
    canEdit: hasPermission(permissions, 'education:foundation:feature-flag:update'),
    enabledAction: hasPermission(permissions, 'education:foundation:feature-flag:status')
      ? enabled ? 'disable' : 'enable'
      : null,
    statusAction: hasPermission(permissions, 'education:foundation:feature-flag:status') && status
      ? status === 'enabled' ? 'disable' : 'enable'
      : null,
    canDelete: !isLocked && hasPermission(permissions, 'education:foundation:feature-flag:delete'),
    canResolve: hasPermission(permissions, 'education:foundation:feature-flag:lookup'),
  }
}
