import type { RouteRecordRaw } from 'vue-router'

const educationRoutes: RouteRecordRaw[] = [
  {
    path: '/education',
    name: 'Education',
    redirect: '/education/foundation/tenants',
    meta: {
      title: '教务 SaaS',
      icon: 'material-symbols:school-outline-rounded',
      type: 'M',
      auth: ['education:foundation:tenant:page', 'education:foundation:campus:page', 'education:foundation:user-profile:page', 'education:foundation:dictionary:page', 'education:foundation:feature-flag:page', 'education:foundation:audit-log:page'],
    },
    children: [
      {
        path: '/education/foundation',
        name: 'EducationFoundation',
        redirect: '/education/foundation/tenants',
        meta: {
          title: '基础设置',
          icon: 'material-symbols:settings-outline-rounded',
          type: 'M',
          auth: ['education:foundation:tenant:page', 'education:foundation:campus:page', 'education:foundation:user-profile:page', 'education:foundation:dictionary:page', 'education:foundation:feature-flag:page', 'education:foundation:audit-log:page'],
        },
        children: [
          {
            path: '/education/foundation/tenants',
            name: 'EducationFoundationTenantList',
            component: () => import('~/education/views/foundation/TenantList.vue'),
            meta: {
              title: '机构',
              icon: 'material-symbols:business-center-outline-rounded',
              type: 'M',
              auth: ['education:foundation:tenant:page'],
              cache: true,
            },
          },
          {
            path: '/education/foundation/campuses',
            name: 'EducationFoundationCampusList',
            component: () => import('~/education/views/foundation/CampusList.vue'),
            meta: {
              title: '校区',
              icon: 'material-symbols:location-city-outline-rounded',
              type: 'M',
              auth: ['education:foundation:campus:page'],
              cache: true,
            },
          },
          {
            path: '/education/foundation/user-profiles',
            name: 'EducationFoundationUserProfileList',
            component: () => import('~/education/views/foundation/UserProfileList.vue'),
            meta: {
              title: '人员权限',
              icon: 'material-symbols:manage-accounts-outline-rounded',
              type: 'M',
              auth: ['education:foundation:user-profile:page'],
              cache: true,
            },
          },
          {
            path: '/education/foundation/dictionaries',
            name: 'EducationFoundationDictionaryList',
            component: () => import('~/education/views/foundation/DictionaryList.vue'),
            meta: {
              title: '字典配置',
              icon: 'material-symbols:format-list-bulleted-rounded',
              type: 'M',
              auth: ['education:foundation:dictionary:page'],
              cache: true,
            },
          },
          {
            path: '/education/foundation/feature-flags',
            name: 'EducationFoundationFeatureFlagList',
            component: () => import('~/education/views/foundation/FeatureFlagList.vue'),
            meta: {
              title: '功能开关',
              icon: 'material-symbols:toggle-on-outline-rounded',
              type: 'M',
              auth: ['education:foundation:feature-flag:page'],
              cache: true,
            },
          },
          {
            path: '/education/foundation/audit-logs',
            name: 'EducationFoundationAuditLogList',
            component: () => import('~/education/views/foundation/AuditLogList.vue'),
            meta: {
              title: 'Audit Logs',
              icon: 'i-lucide-file-clock',
              type: 'M',
              auth: ['education:foundation:audit-log:page'],
              cache: true,
            },
          },
        ],
      },
    ],
  },
]

export default educationRoutes
