import useUserStore from '@/store/modules/useUserStore.ts'
import type { EducationPermissionCode } from './educationPermissionRules.ts'
import { hasAnyEducationPermission, hasEducationPermission } from './educationPermissionRules.ts'

export type { EducationPermissionCode }
export { hasAnyEducationPermission, hasEducationPermission }

export function useEducationPermission(): {
  has: (code: EducationPermissionCode) => boolean
  hasAny: (codes: EducationPermissionCode[]) => boolean
} {
  const userStore = useUserStore()
  const permissions = computed(() => userStore.getPermissions())

  return {
    has: code => hasEducationPermission(permissions.value, code),
    hasAny: codes => hasAnyEducationPermission(permissions.value, codes),
  }
}
