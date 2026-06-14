import type { GuardianFoundationContext, MobileFoundationContext } from '@/api/foundation/types'
import type { FoundationPageOptions } from './useFoundationContextPage'

export const teacherPageOptions: FoundationPageOptions<MobileFoundationContext> = {
  emptyMessage: (context) => {
    if (context.campus_scopes.length === 0) {
      return 'No campus scope assigned'
    }
    if (!hasEnabledFeatures(context)) {
      return 'No enabled mobile features'
    }

    return null
  },
}

export const guardianPageOptions: FoundationPageOptions<GuardianFoundationContext> = {
  emptyMessage: (context) => {
    if (context.empty_state !== null) {
      return context.empty_state.message
    }
    if (context.bound_students.length === 0) {
      return 'Student binding will be available in V1'
    }

    return null
  },
  forbiddenMessage: (error) => {
    const message = (error as { message?: string })?.message

    return message === 'guardian profile is not bound'
      ? 'Contact campus to bind guardian profile'
      : null
  },
}

export const operatorPageOptions: FoundationPageOptions<MobileFoundationContext> = {
  emptyMessage: (context) => {
    if (context.profile.role_code !== 'tenant_admin' && context.campus_scopes.length === 0) {
      return 'No campus scope assigned'
    }
    if (!hasEnabledFeatures(context)) {
      return 'No enabled mobile features'
    }

    return null
  },
}

function hasEnabledFeatures(context: MobileFoundationContext): boolean {
  return Object.values(context.feature_flags).some(Boolean)
}
