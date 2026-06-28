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
    throw new Error(`${fieldName} 必须是 JSON 对象`)
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

const educationRoleLabels: Record<EducationRoleCode, string> = {
  platform_super_admin: '平台超级管理员',
  platform_operator: '平台运营',
  tenant_admin: '机构管理员',
  principal: '校长',
  academic_staff: '教务',
  front_desk: '前台',
  teacher: '教师',
  finance: '财务',
  guardian: '家长',
}

const foundationStatusLabels: Record<EducationStatus | FoundationStatus, string> = {
  enabled: '启用',
  disabled: '停用',
}

export function educationRoleLabel(roleCode: EducationRoleCode): string {
  return educationRoleLabels[roleCode] ?? roleCode
}

export function educationRoleOptions(): Array<{ label: string, value: EducationRoleCode }> {
  return Object.entries(educationRoleLabels).map(([value, label]) => ({
    label,
    value: value as EducationRoleCode,
  }))
}

export function foundationStatusLabel(status: EducationStatus | FoundationStatus): string {
  return foundationStatusLabels[status] ?? status
}

export function foundationStatusOptions(): Array<{ label: string, value: FoundationStatus }> {
  return [
    { label: foundationStatusLabel('enabled'), value: 'enabled' },
    { label: foundationStatusLabel('disabled'), value: 'disabled' },
  ]
}

export function configOwnerTypeLabel(ownerType: ConfigOwnerType): string {
  return ownerType === 'system' ? '系统' : '机构'
}

export function enabledFlagLabel(enabled: boolean): string {
  return enabled ? '开启' : '关闭'
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
    ? '请选择校区范围'
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
  const tenantOption = { label: configOwnerTypeLabel('tenant'), value: 'tenant' as const }

  return isPlatformContext
    ? [{ label: configOwnerTypeLabel('system'), value: 'system' as const }, tenantOption]
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
