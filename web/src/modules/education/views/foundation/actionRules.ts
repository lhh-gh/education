import type { EducationStatus } from '../../api/foundation/tenant.ts'
import type { ConfigOwnerType, DictItemPageParams, DictTypePageParams, DictTypeRecord, FoundationStatus } from '../../api/foundation/dictionary.ts'
import type { FeatureFlagPageParams } from '../../api/foundation/featureFlag.ts'
import type { EducationRoleCode } from '../../api/foundation/userProfile.ts'

export function hasPermission(permissions: string[], code: string): boolean {
  return permissions.includes('*') || permissions.includes('education:*') || permissions.includes(code)
}

export function hasPlatformConfigPermission(permissions: string[]): boolean {
  return permissions.includes('*') || permissions.includes('education:*')
}

export function extractApiErrorMessage(error: unknown, fallback: string): string {
  if (error && typeof error === 'object' && 'message' in error && typeof error.message === 'string') {
    return error.message
  }

  return fallback
}

export function isSubmitDisabled(submitting: boolean): boolean {
  return submitting
}

export function shouldCloseFormAfterSubmit(success: boolean): boolean {
  return success
}

export function keepListStateAfterError<TFilters extends Record<string, unknown>, TRow>(
  state: { filters: TFilters, rows: TRow[], total: number },
) {
  return {
    filters: state.filters,
    rows: state.rows,
    total: state.total,
  }
}

export function parseJsonObjectText(text: string, fieldName: string): Record<string, unknown> | undefined {
  const trimmed = text.trim()

  if (!trimmed) {
    return undefined
  }

  const parsed = JSON.parse(trimmed)
  if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
    throw new Error(`${fieldName} must be a JSON object`)
  }

  return parsed as Record<string, unknown>
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
  const canMutateLocked = !isLocked || hasPlatformConfigPermission(permissions)

  return {
    canCreateType: hasPermission(permissions, 'education:foundation:dictionary:create'),
    canEditType: canMutateLocked && hasPermission(permissions, 'education:foundation:dictionary:update'),
    typeStatusAction: canMutateLocked && hasPermission(permissions, 'education:foundation:dictionary:status') && typeStatus
      ? typeStatus === 'enabled' ? 'disable' : 'enable'
      : null,
    canDeleteType: canMutateLocked && hasPermission(permissions, 'education:foundation:dictionary:delete'),
    canCreateItem: hasPermission(permissions, 'education:foundation:dictionary-item:create'),
    canEditItem: canMutateLocked && hasPermission(permissions, 'education:foundation:dictionary-item:update'),
    itemStatusAction: canMutateLocked && hasPermission(permissions, 'education:foundation:dictionary-item:status') && itemStatus
      ? itemStatus === 'enabled' ? 'disable' : 'enable'
      : null,
    canDeleteItem: canMutateLocked && hasPermission(permissions, 'education:foundation:dictionary-item:delete'),
  }
}

export function dictionaryOwnerTypeOptions(isPlatformContext: boolean): Array<{ label: string, value: ConfigOwnerType }> {
  const tenantOption = { label: '租户', value: 'tenant' as const }

  return isPlatformContext
    ? [{ label: '系统', value: 'system' as const }, tenantOption]
    : [tenantOption]
}

export function defaultDictTypeSearch(isPlatformContext = true): DictTypePageParams {
  return {
    page: 1,
    page_size: 20,
    owner_type: isPlatformContext ? undefined : 'tenant',
    tenant_id: undefined,
    keyword: '',
    status: undefined,
  }
}

export function normalizeDictTypeSearch(search: DictTypePageParams): DictTypePageParams {
  return {
    ...search,
    page: search.page || 1,
    page_size: search.page_size || 20,
    tenant_id: search.owner_type === 'system' ? undefined : search.tenant_id,
  }
}

export function defaultDictItemSearch(): DictItemPageParams {
  return {
    page: 1,
    page_size: 20,
    keyword: '',
    status: undefined,
  }
}

export function dictionaryItemParamsForType(search: DictItemPageParams, selectedType: DictTypeRecord): DictItemPageParams {
  return {
    ...search,
    page: search.page || 1,
    page_size: search.page_size || 20,
    dict_type_id: selectedType.id,
    dict_code: selectedType.code,
  }
}

export type FeatureFlagDateRange = [string, string] | []

export function defaultFeatureFlagSearch(): FeatureFlagPageParams {
  return {
    page: 1,
    page_size: 20,
    owner_type: undefined,
    tenant_id: undefined,
    keyword: '',
    enabled: undefined,
    status: undefined,
  }
}

export function normalizeFeatureFlagSearch(search: FeatureFlagPageParams, dateRange: FeatureFlagDateRange = []): FeatureFlagPageParams {
  const params: FeatureFlagPageParams = {
    ...search,
    page: search.page || 1,
    page_size: search.page_size || 20,
    tenant_id: search.owner_type === 'system' ? undefined : search.tenant_id,
  }

  if (dateRange.length === 2) {
    params.effective_from = dateRange[0]
    params.effective_to = dateRange[1]
  }
  else {
    delete params.effective_from
    delete params.effective_to
  }

  return params
}

export function resetFeatureFlagSearch(): { search: FeatureFlagPageParams, dateRange: FeatureFlagDateRange } {
  return {
    search: defaultFeatureFlagSearch(),
    dateRange: [],
  }
}

export function featureFlagActionsByPermission(permissions: string[], enabled: boolean, isLocked: boolean, status?: FoundationStatus) {
  const canMutateLocked = !isLocked || hasPlatformConfigPermission(permissions)

  return {
    canCreate: hasPermission(permissions, 'education:foundation:feature-flag:create'),
    canEdit: canMutateLocked && hasPermission(permissions, 'education:foundation:feature-flag:update'),
    enabledAction: canMutateLocked && hasPermission(permissions, 'education:foundation:feature-flag:status')
      ? enabled ? 'disable' : 'enable'
      : null,
    statusAction: canMutateLocked && hasPermission(permissions, 'education:foundation:feature-flag:status') && status
      ? status === 'enabled' ? 'disable' : 'enable'
      : null,
    canDelete: canMutateLocked && hasPermission(permissions, 'education:foundation:feature-flag:delete'),
    canResolve: hasPermission(permissions, 'education:foundation:feature-flag:lookup'),
  }
}
