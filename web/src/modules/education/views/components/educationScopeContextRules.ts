import type { EducationScopeState } from '@/composables/education/useEducationScope.ts'
import { setEducationScope } from '@/composables/education/useEducationScope.ts'
import type { CampusListItem } from '../../api/foundation/campus.ts'
import type { TenantListItem } from '../../api/foundation/tenant.ts'

export interface EducationScopeOption {
  label: string
  value: number
  disabled: boolean
}

function optionLabel(name: string, code?: string): string {
  return code ? `${name}（${code}）` : name
}

function isPositiveId(value?: number): value is number {
  return typeof value === 'number' && Number.isFinite(value) && value > 0
}

export function buildTenantScopeOptions(items: TenantListItem[]): EducationScopeOption[] {
  return items.map(item => ({
    label: optionLabel(item.name, item.code),
    value: item.id,
    disabled: item.status !== 'enabled',
  }))
}

export function buildCampusScopeOptions(items: CampusListItem[]): EducationScopeOption[] {
  return items.map(item => ({
    label: optionLabel(item.name, item.code),
    value: item.id,
    disabled: item.status !== 'enabled',
  }))
}

export function resolveEducationScopePayload(tenantId?: number, campusId?: number): EducationScopeState {
  const payload: EducationScopeState = {
    tenant_id: isPositiveId(tenantId) ? tenantId : undefined,
    campus_id: isPositiveId(campusId) ? campusId : undefined,
  }

  return setEducationScope(payload)
}

export function shouldShowEducationScopeContext(path?: string): boolean {
  return typeof path === 'string' && path.startsWith('/education')
}
