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
      auth: ['education:foundation:tenant:page', 'education:foundation:campus:page'],
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
          auth: ['education:foundation:tenant:page', 'education:foundation:campus:page'],
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
        ],
      },
    ],
  },
]

export default educationRoutes
