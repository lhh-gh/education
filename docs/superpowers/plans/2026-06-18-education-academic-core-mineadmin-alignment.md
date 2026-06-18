# Education Academic Core MineAdmin Alignment Implementation Plan

> **For agentic workers:** Use the executing-plans skill to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Align the first academic management batch with the MineAdmin-style Chinese admin experience after the AI Assistant batch.

**Scope:** This batch covers the academic core master-data pages and their edit forms only: dashboard, classrooms, students, guardians, teachers, courses, lesson packages, classes, and shared label helpers. Schedule, attendance, leave, account, enrollment, notice, and report pages remain follow-up batches.

**Architecture:** Keep route paths, route names, component names, API client paths, permission codes, request fields, and response fields stable. Move shared user-facing status, gender, relation, and action labels into existing academic rules files, then let the covered pages consume those helpers.

**Tech Stack:** MineAdmin Vue 3, Element Plus, Vitest, TypeScript.

---

### Task 1: Academic Core Chinese Text Rules

**Files:**
- Modify: `web/src/modules/education/views/academic/actionRules.ts`
- Modify: `web/src/modules/education/views/academic/reportRules.ts`
- Test: `web/src/modules/education/views/academic/__tests__/AcademicCoreLocalization.spec.ts`
- Test: `web/src/modules/education/views/academic/__tests__/AcademicDashboard.spec.ts`

- [x] **Step 1: Write failing tests**

Assert shared Chinese labels:

```ts
expect(academicStatusLabel('enabled')).toBe('启用')
expect(academicStatusLabel('disabled')).toBe('停用')
expect(academicGenderLabel('male')).toBe('男')
expect(relationLabel('father')).toBe('父亲')
expect(academicActionText('enabled')).toBe('停用')
expect(dashboardMetricItems({}).map(item => item.title)).toContain('在读学员')
```

- [x] **Step 2: Verify red**

Run:

```bash
cd web && pnpm test src/modules/education/views/academic/__tests__/AcademicCoreLocalization.spec.ts src/modules/education/views/academic/__tests__/AcademicDashboard.spec.ts
```

Expected: fails because helpers or Chinese dashboard metric titles are not implemented yet.

- [x] **Step 3: Implement helper labels**

Add Chinese status, gender, relation, action, and dashboard metric labels. Keep business enum values unchanged.

- [x] **Step 4: Verify green**

Run the same command and confirm it passes.

### Task 2: Academic Core Page Chinese MineAdmin Copy

**Files:**
- Modify: `web/src/modules/education/views/academic/AcademicDashboard.vue`
- Modify: `web/src/modules/education/views/academic/ClassroomList.vue`
- Modify: `web/src/modules/education/views/academic/StudentList.vue`
- Modify: `web/src/modules/education/views/academic/GuardianList.vue`
- Modify: `web/src/modules/education/views/academic/TeacherList.vue`
- Modify: `web/src/modules/education/views/academic/CourseList.vue`
- Modify: `web/src/modules/education/views/academic/LessonPackageList.vue`
- Modify: `web/src/modules/education/views/academic/ClassList.vue`
- Modify: `web/src/modules/education/views/academic/components/ClassroomForm.vue`
- Modify: `web/src/modules/education/views/academic/components/StudentForm.vue`
- Modify: `web/src/modules/education/views/academic/components/GuardianForm.vue`
- Modify: `web/src/modules/education/views/academic/components/TeacherForm.vue`
- Modify: `web/src/modules/education/views/academic/components/CourseForm.vue`
- Modify: `web/src/modules/education/views/academic/components/LessonPackageForm.vue`
- Modify: `web/src/modules/education/views/academic/components/ClassForm.vue`
- Test: `web/src/modules/education/views/academic/__tests__/AcademicCoreLocalization.spec.ts`

- [x] **Step 1: Write failing page-copy tests**

Scan the covered Vue pages and form components and assert legacy visible English strings such as `Classrooms`, `Students`, `Lesson Packages`, `New`, `Search`, `Reset`, `Actions`, `No classrooms`, `Delete this classroom?`, `Campus is required`, and `Save` are absent.

- [x] **Step 2: Verify red**

Run:

```bash
cd web && pnpm test src/modules/education/views/academic/__tests__/AcademicCoreLocalization.spec.ts
```

Expected: fails against the current English copy.

- [x] **Step 3: Localize covered pages**

Replace visible page titles, query labels, buttons, table columns, empty states, dialog titles, confirm prompts, and default error messages with Chinese. Use shared helpers for status, gender, and action labels.

- [x] **Step 4: Verify green**

Run the same command and confirm it passes.

### Task 3: Verification And Commit

**Files:**
- Modify only files touched by Tasks 1 and 2 plus this plan.

- [x] **Step 1: Run academic core tests**

```bash
cd web && pnpm test src/modules/education/views/academic/__tests__/AcademicCoreLocalization.spec.ts src/modules/education/views/academic/__tests__/AcademicDashboard.spec.ts src/modules/education/views/academic/__tests__/ClassroomList.spec.ts src/modules/education/views/academic/__tests__/StudentList.spec.ts src/modules/education/views/academic/__tests__/GuardianList.spec.ts src/modules/education/views/academic/__tests__/TeacherList.spec.ts src/modules/education/views/academic/__tests__/CourseList.spec.ts src/modules/education/views/academic/__tests__/LessonPackageList.spec.ts src/modules/education/views/academic/__tests__/ClassList.spec.ts
```

- [x] **Step 2: Run frontend typecheck**

```bash
cd web && pnpm typecheck
```

- [x] **Step 3: Inspect git status**

```bash
git status --short
```

Expected: `.gitignore` remains unstaged user work; staged files must be limited to this academic core batch.

- [x] **Step 4: Commit and push**

```bash
git add docs/superpowers/plans/2026-06-18-education-academic-core-mineadmin-alignment.md \
  web/src/modules/education/views/academic/actionRules.ts \
  web/src/modules/education/views/academic/reportRules.ts \
  web/src/modules/education/views/academic/AcademicDashboard.vue \
  web/src/modules/education/views/academic/ClassroomList.vue \
  web/src/modules/education/views/academic/StudentList.vue \
  web/src/modules/education/views/academic/GuardianList.vue \
  web/src/modules/education/views/academic/TeacherList.vue \
  web/src/modules/education/views/academic/CourseList.vue \
  web/src/modules/education/views/academic/LessonPackageList.vue \
  web/src/modules/education/views/academic/ClassList.vue \
  web/src/modules/education/views/academic/components/ClassroomForm.vue \
  web/src/modules/education/views/academic/components/StudentForm.vue \
  web/src/modules/education/views/academic/components/GuardianForm.vue \
  web/src/modules/education/views/academic/components/TeacherForm.vue \
  web/src/modules/education/views/academic/components/CourseForm.vue \
  web/src/modules/education/views/academic/components/LessonPackageForm.vue \
  web/src/modules/education/views/academic/components/ClassForm.vue \
  web/src/modules/education/views/academic/__tests__/AcademicCoreLocalization.spec.ts \
  web/src/modules/education/views/academic/__tests__/AcademicDashboard.spec.ts
git commit -m "fix(education): 对齐教务核心后台页面"
git push origin f00-environment
```
