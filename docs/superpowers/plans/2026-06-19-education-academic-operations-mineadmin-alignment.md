# Education Academic Operations MineAdmin Alignment Implementation Plan

> **For agentic workers:** Use the executing-plans skill to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Finish the academic management pages that were left out of the previous core-data batch, especially enrollment, course accounts, scheduling, lessons, attendance review, consumption, leave, lesson changes, notices, and reports.

**Scope:** This batch covers the remaining top-level academic operations pages:

- `AcademicDashboard.vue`
- `EnrollmentWorkbench.vue`
- `AccountLedgerList.vue`
- `AccountBalanceReport.vue`
- `LessonScheduleCalendar.vue`
- `LessonList.vue`
- `AttendanceReview.vue`
- `ConsumptionLedgerList.vue`
- `AccountAdjustmentList.vue`
- `LeaveRequestList.vue`
- `LessonChangeList.vue`
- `AttendanceReport.vue`
- `ConsumptionReport.vue`
- `LeaveReport.vue`
- `NoticeList.vue`
- `V1AcceptanceReport.vue`
- shared academic report filter/state/toolbar components
- shared academic operation label helpers

**Architecture:** Keep routes, component names, API clients, permissions, query fields, enum values, and backend contracts unchanged. Convert only user-visible labels, table columns, empty states, prompts, success/error messages, and enum display labels to Chinese. Continue using the existing MineAdmin page structure: `mine-layout ... pt-3`, `el-card shadow="never"`, inline query forms, `el-table`, pagination, dialogs/drawers, and `hasAuth` controlled actions.

---

### Task 1: Operation Label Helpers

**Files:**
- Modify: `web/src/modules/education/views/academic/courseAccountRules.ts`
- Modify: `web/src/modules/education/views/academic/classScheduleRules.ts`
- Modify: `web/src/modules/education/views/academic/attendanceConsumptionRules.ts`
- Modify: `web/src/modules/education/views/academic/leaveMakeupRescheduleRules.ts`
- Modify: `web/src/modules/education/views/academic/noticeRules.ts`
- Modify: `web/src/modules/education/views/academic/reportRules.ts`
- Test: `web/src/modules/education/views/academic/__tests__/AcademicOperationsLocalization.spec.ts`

- [x] **Step 1: Write failing tests**

Assert operation labels are Chinese:

```ts
expect(enrollmentStatusLabel('confirmed')).toBe('已确认')
expect(accountStatusLabel('active')).toBe('正常')
expect(lessonStatusLabel('scheduled')).toBe('待上课')
expect(attendanceStatusLabel('present')).toBe('出勤')
expect(leaveStatusLabel('pending')).toBe('待审批')
expect(noticeStatusLabel('published')).toBe('已发布')
expect(summaryMetricItems({}, ['total_records'])[0].title).toBe('记录数')
```

- [x] **Step 2: Verify red**

Run:

```bash
cd web && pnpm test src/modules/education/views/academic/__tests__/AcademicOperationsLocalization.spec.ts
```

Expected: fails because helpers or Chinese labels do not exist yet.

- [x] **Step 3: Implement helpers**

Add stable Chinese mapping helpers while keeping enum values unchanged.

- [x] **Step 4: Verify green**

Run the same test file and confirm it passes.

### Task 2: Academic Operations Page Copy

**Files:** all scoped pages and shared report components above plus the same test file.

- [x] **Step 1: Add failing scan assertions**

Scan scoped top-level pages and assert legacy visible English copy such as `Enrollments`, `Course Accounts`, `Lesson Schedule`, `Lessons`, `Attendance Review`, `Search`, `Reset`, `Actions`, `No lessons`, `Cancel reason`, and report titles are absent.

- [x] **Step 2: Verify red**

Run:

```bash
cd web && pnpm test src/modules/education/views/academic/__tests__/AcademicOperationsLocalization.spec.ts
```

- [x] **Step 3: Localize scoped pages**

Replace page titles, query labels, buttons, table columns, empty states, prompts, success/error messages, report toolbar titles, and enum display labels with Chinese. Use helper functions for status/type/source labels where practical.

- [x] **Step 4: Verify green**

Run the same test file and confirm it passes.

### Task 3: Verification And Commit

- [x] **Step 1: Run focused academic operation tests**

```bash
cd web && pnpm test src/modules/education/views/academic/__tests__/AcademicOperationsLocalization.spec.ts src/modules/education/views/academic/__tests__/AcademicDashboard.spec.ts src/modules/education/views/academic/components/__tests__/ReportDateRangeFilter.spec.ts src/modules/education/views/academic/components/__tests__/ReportStateBlock.spec.ts src/modules/education/views/academic/__tests__/EnrollmentWorkbench.spec.ts src/modules/education/views/academic/__tests__/AccountLedgerList.spec.ts src/modules/education/views/academic/__tests__/AccountBalanceReport.spec.ts src/modules/education/views/academic/__tests__/LessonScheduleCalendar.spec.ts src/modules/education/views/academic/__tests__/LessonList.spec.ts src/modules/education/views/academic/__tests__/AttendanceReview.spec.ts src/modules/education/views/academic/__tests__/ConsumptionLedgerList.spec.ts src/modules/education/views/academic/__tests__/AccountAdjustmentList.spec.ts src/modules/education/views/academic/__tests__/LeaveRequestList.spec.ts src/modules/education/views/academic/__tests__/LessonChangeList.spec.ts src/modules/education/views/academic/__tests__/AttendanceReport.spec.ts src/modules/education/views/academic/__tests__/ConsumptionReport.spec.ts src/modules/education/views/academic/__tests__/LeaveReport.spec.ts src/modules/education/views/academic/__tests__/NoticeList.spec.ts src/modules/education/views/academic/__tests__/V1AcceptanceReport.spec.ts
```

- [x] **Step 2: Run frontend typecheck**

```bash
cd web && pnpm typecheck
```

- [x] **Step 3: Inspect git status**

```bash
git status --short
```

Expected: `.gitignore` remains unstaged user work; staged files are limited to this batch.

- [x] **Step 4: Commit and push**

```bash
git commit -m "fix(education): 对齐教务运营后台页面"
git push origin f00-environment
```
