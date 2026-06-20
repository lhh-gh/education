import { defineStore } from 'pinia'
import { computed } from 'vue'
import {
  clearEducationScope,
  getEducationScopeSnapshot,
  patchEducationScope,
  setEducationScope,
  useEducationScope,
  type EducationScopeState,
} from '@/composables/education/useEducationScope.ts'

const useEducationScopeStore = defineStore('useEducationScopeStore', () => {
  const { scope } = useEducationScope()
  const tenantId = computed(() => scope.tenant_id)
  const campusId = computed(() => scope.campus_id)
  const hasTenant = computed(() => typeof tenantId.value === 'number')
  const hasCampus = computed(() => typeof campusId.value === 'number')

  function getScope(): EducationScopeState {
    return getEducationScopeSnapshot()
  }

  function setScope(input: EducationScopeState): EducationScopeState {
    return setEducationScope(input)
  }

  function patchScope(input: EducationScopeState): EducationScopeState {
    return patchEducationScope(input)
  }

  function clearScope(): EducationScopeState {
    return clearEducationScope()
  }

  return {
    scope,
    tenantId,
    campusId,
    hasTenant,
    hasCampus,
    getScope,
    setScope,
    patchScope,
    clearScope,
  }
})

export default useEducationScopeStore
