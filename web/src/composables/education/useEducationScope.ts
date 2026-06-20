import { computed, readonly, reactive } from 'vue'

export interface EducationScopeState {
  tenant_id?: number
  campus_id?: number
}

const state = reactive<EducationScopeState>({
  tenant_id: undefined,
  campus_id: undefined,
})

function normalizeScopeId(value?: number): number | undefined {
  return typeof value === 'number' && Number.isFinite(value) && value > 0 ? value : undefined
}

export function getEducationScopeSnapshot(): EducationScopeState {
  return {
    tenant_id: state.tenant_id,
    campus_id: state.campus_id,
  }
}

export function setEducationScope(input: EducationScopeState): EducationScopeState {
  state.tenant_id = normalizeScopeId(input.tenant_id)
  state.campus_id = normalizeScopeId(input.campus_id)

  return getEducationScopeSnapshot()
}

export function patchEducationScope(input: EducationScopeState): EducationScopeState {
  if ('tenant_id' in input) {
    state.tenant_id = normalizeScopeId(input.tenant_id)
  }

  if ('campus_id' in input) {
    state.campus_id = normalizeScopeId(input.campus_id)
  }

  return getEducationScopeSnapshot()
}

export function clearEducationScope(): EducationScopeState {
  state.tenant_id = undefined
  state.campus_id = undefined

  return getEducationScopeSnapshot()
}

export function useEducationScope() {
  return {
    scope: readonly(state),
    snapshot: computed(getEducationScopeSnapshot),
    setScope: setEducationScope,
    patchScope: patchEducationScope,
    clearScope: clearEducationScope,
  }
}
