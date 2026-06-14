export type MobileClientType = 'wechat_service' | 'wechat_miniprogram' | 'h5'

export type MobileRoleCode =
  | 'teacher'
  | 'guardian'
  | 'tenant_admin'
  | 'principal'
  | 'academic_staff'
  | 'front_desk'
  | 'finance'

export interface MobileTenant {
  id: number
  name: string
  short_name: string | null
}

export interface MobileProfile {
  id: number
  user_id: number
  role_code: MobileRoleCode
  display_name: string
  mobile: string | null
  avatar: string | null
  current_campus_id: number | null
}

export interface MobileCampusScope {
  campus_id: number
  campus_name: string
  is_current: boolean
}

export interface MobileEntryTab {
  key: string
  label: string
  path: string
}

export interface MobileEmptyState {
  code: string
  message: string
}

export interface MobileFoundationContext {
  tenant: MobileTenant
  profile: MobileProfile
  campus_scopes: MobileCampusScope[]
  feature_flags: Record<string, boolean>
  entry: {
    default_path: string
    tabs: MobileEntryTab[]
  }
  empty_state: MobileEmptyState | null
}

export interface GuardianFoundationContext extends MobileFoundationContext {
  bound_students: Array<{ id: number; name: string }>
}
