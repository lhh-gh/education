export type EducationPermissionCode
  = | 'education:foundation:tenant:page'
    | 'education:foundation:tenant:create'
    | 'education:foundation:tenant:update'
    | 'education:foundation:tenant:status'
    | 'education:foundation:tenant:delete'
    | 'education:foundation:campus:page'
    | 'education:foundation:campus:create'
    | 'education:foundation:campus:update'
    | 'education:foundation:campus:status'
    | 'education:foundation:campus:delete'
    | 'education:foundation:user-profile:page'
    | 'education:foundation:user-profile:create'
    | 'education:foundation:user-profile:update'
    | 'education:foundation:user-profile:status'
    | 'education:foundation:campus-scope:page'
    | 'education:foundation:campus-scope:save'
    | 'education:foundation:dictionary:page'
    | 'education:foundation:dictionary:create'
    | 'education:foundation:dictionary:update'
    | 'education:foundation:dictionary:status'
    | 'education:foundation:dictionary:delete'
    | 'education:foundation:dictionary-item:page'
    | 'education:foundation:dictionary-item:create'
    | 'education:foundation:dictionary-item:update'
    | 'education:foundation:dictionary-item:status'
    | 'education:foundation:dictionary-item:delete'
    | 'education:foundation:dictionary-item:lookup'
    | 'education:foundation:feature-flag:page'
    | 'education:foundation:feature-flag:create'
    | 'education:foundation:feature-flag:update'
    | 'education:foundation:feature-flag:status'
    | 'education:foundation:feature-flag:delete'
    | 'education:foundation:feature-flag:lookup'
    | 'education:foundation:audit-log:page'
    | 'education:foundation:audit-log:detail'

export function hasEducationPermission(permissions: string[], code: EducationPermissionCode): boolean {
  return permissions.includes('*') || permissions.includes('education:*') || permissions.includes(code)
}

export function hasAnyEducationPermission(permissions: string[], codes: EducationPermissionCode[]): boolean {
  return codes.some(code => hasEducationPermission(permissions, code))
}
