import type { RouteRecordRaw } from 'vue-router'

const educationRoutes: RouteRecordRaw[] = [
  {
    path: '/education',
    name: 'EducationRoot',
    redirect: '/education/foundation/tenants',
    meta: {
      title: '教务 SaaS',
      icon: 'material-symbols:school-outline-rounded',
      type: 'M',
      auth: ['education:*'],
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
      {
        path: '/education/academic',
        name: 'EducationAcademic',
        redirect: '/education/academic/classrooms',
        meta: {
          title: 'V1 Academic',
          icon: 'material-symbols:auto-stories-outline-rounded',
          type: 'M',
          auth: ['education:academic:classroom:page', 'education:academic:student:page', 'education:academic:guardian:page', 'education:academic:teacher:page', 'education:academic:course:page', 'education:academic:lesson-package:page', 'education:academic:enrollment:page', 'education:academic:student-course-account:page'],
        },
        children: [
          {
            path: '/education/academic/classrooms',
            name: 'EducationAcademicClassroomList',
            component: () => import('~/education/views/academic/ClassroomList.vue'),
            meta: {
              title: 'Classrooms',
              icon: 'material-symbols:meeting-room-outline-rounded',
              type: 'M',
              auth: ['education:academic:classroom:page'],
              cache: true,
            },
          },
          {
            path: '/education/academic/students',
            name: 'EducationAcademicStudentList',
            component: () => import('~/education/views/academic/StudentList.vue'),
            meta: {
              title: 'Students',
              icon: 'material-symbols:face-3-outline-rounded',
              type: 'M',
              auth: ['education:academic:student:page'],
              cache: true,
            },
          },
          {
            path: '/education/academic/guardians',
            name: 'EducationAcademicGuardianList',
            component: () => import('~/education/views/academic/GuardianList.vue'),
            meta: {
              title: 'Guardians',
              icon: 'material-symbols:family-restroom-rounded',
              type: 'M',
              auth: ['education:academic:guardian:page'],
              cache: true,
            },
          },
          {
            path: '/education/academic/teachers',
            name: 'EducationAcademicTeacherList',
            component: () => import('~/education/views/academic/TeacherList.vue'),
            meta: {
              title: 'Teachers',
              icon: 'material-symbols:co-present-outline-rounded',
              type: 'M',
              auth: ['education:academic:teacher:page'],
              cache: true,
            },
          },
          {
            path: '/education/academic/courses',
            name: 'EducationAcademicCourseList',
            component: () => import('~/education/views/academic/CourseList.vue'),
            meta: {
              title: 'Courses',
              icon: 'material-symbols:menu-book-outline-rounded',
              type: 'M',
              auth: ['education:academic:course:page'],
              cache: true,
            },
          },
          {
            path: '/education/academic/lesson-packages',
            name: 'EducationAcademicLessonPackageList',
            component: () => import('~/education/views/academic/LessonPackageList.vue'),
            meta: {
              title: 'Lesson Packages',
              icon: 'material-symbols:inventory-2-outline-rounded',
              type: 'M',
              auth: ['education:academic:lesson-package:page'],
              cache: true,
            },
          },
          {
            path: '/education/academic/enrollments',
            name: 'EducationAcademicEnrollmentWorkbench',
            component: () => import('~/education/views/academic/EnrollmentWorkbench.vue'),
            meta: {
              title: 'Enrollments',
              icon: 'material-symbols:how-to-reg-outline-rounded',
              type: 'M',
              auth: ['education:academic:enrollment:page'],
              cache: true,
            },
          },
          {
            path: '/education/academic/course-accounts',
            name: 'EducationAcademicAccountLedgerList',
            component: () => import('~/education/views/academic/AccountLedgerList.vue'),
            meta: {
              title: 'Course Accounts',
              icon: 'material-symbols:account-balance-wallet-outline-rounded',
              type: 'M',
              auth: ['education:academic:student-course-account:page'],
              cache: true,
            },
          },
        ],
      },
    ],
  },
]

export default educationRoutes
