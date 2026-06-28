# Standards MineAdmin Alignment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert the remaining Education Standards placeholder pages into MineAdmin-style, Chinese, permission-aware operational pages.

**Architecture:** Keep the existing education standards API clients and add only the minimal typed list helpers needed by pages. Pages stay inside `web/src/modules/education/views/standards`, follow the `mine-layout pt-3` + `el-card` + search/form/table/pagination pattern, and continue using `hasAuth` for mutation buttons.

**Tech Stack:** Vue 3 `<script setup>`, Element Plus, Vitest static/behavior checks, existing MineAdmin `useHttp` API clients, Hyperf/PHP backend only if a required page endpoint is missing.

---

### Task 1: Standards Gap Tests

**Files:**
- Modify: `web/src/modules/education/views/standards/__tests__/StandardsApiClients.spec.ts`
- Create or modify: `web/src/modules/education/views/standards/__tests__/StandardsMineAdminAlignment.spec.ts`

- [ ] **Step 1: Write failing tests**

Add assertions that standards placeholder pages call real API helpers, render MineAdmin search/table or form sections, include Chinese empty states, and guard mutation actions with `hasAuth`.

- [ ] **Step 2: Run tests to verify RED**

Run:

```bash
cd /home/phpgo/projects/www/education/web
source ~/.nvm/nvm.sh
npm test -- src/modules/education/views/standards/__tests__/StandardsApiClients.spec.ts src/modules/education/views/standards/__tests__/StandardsMineAdminAlignment.spec.ts
```

Expected: FAIL because the remaining placeholder pages do not yet contain the required real API calls and MineAdmin structures.

### Task 2: Standards API Typing

**Files:**
- Modify: `web/src/modules/education/api/standards/delivery-standard.ts`
- Modify: `web/src/modules/education/api/standards/trial-standard.ts`
- Modify: `web/src/modules/education/api/standards/template.ts`
- Modify: `web/src/modules/education/api/standards/version.ts`
- Modify: `web/src/modules/education/api/standards/quality.ts`

- [ ] **Step 1: Add minimal typed records and page helpers**

Add page/list helper functions only where the backend route already exists. Preserve current endpoint paths and scope request options.

- [ ] **Step 2: Run standards API tests**

Run the same standards Vitest command and confirm API-path tests pass for newly typed helpers.

### Task 3: Standards Pages

**Files:**
- Modify: `web/src/modules/education/views/standards/AbilityPointList.vue`
- Modify: `web/src/modules/education/views/standards/TrialStandardEditor.vue`
- Modify: `web/src/modules/education/views/standards/DeliveryStandardEditor.vue`
- Modify: `web/src/modules/education/views/standards/ServiceTemplateList.vue`
- Modify: `web/src/modules/education/views/standards/CourseFeedbackList.vue`
- Modify: `web/src/modules/education/views/standards/StandardVersionList.vue`
- Modify: `web/src/modules/education/views/standards/StandardReviewList.vue`

- [ ] **Step 1: Implement minimal MineAdmin pages**

Use existing standards APIs. For save-only endpoints, keep an inline save form and local rows when no backend page route exists. For existing page endpoints, load real paginated rows.

- [ ] **Step 2: Guard mutation buttons**

Use `hasAuth` for `education:standards:*:save`, `education:standards:version:publish`, `education:standards:version:localization`, and `education:standards:review:handle`.

- [ ] **Step 3: Run RED-to-GREEN standards tests**

Run:

```bash
cd /home/phpgo/projects/www/education/web
source ~/.nvm/nvm.sh
npm test -- src/modules/education/views/standards
```

Expected: PASS for standards tests.

### Task 4: Verification and Commit

**Files:**
- All files changed above.

- [ ] **Step 1: Run frontend checks**

```bash
cd /home/phpgo/projects/www/education/web
source ~/.nvm/nvm.sh
npm test -- src/modules/education/views/standards
npx eslint src/modules/education --quiet
npx stylelint "src/modules/education/**/*.{css,scss,vue}" --quiet
npm run typecheck
```

- [ ] **Step 2: Run backend static checks if backend files changed**

```bash
cd /home/phpgo/projects/www/education
composer cs-fix
composer analyse
```

- [ ] **Step 3: Commit in Chinese**

```bash
git add docs/superpowers/plans/2026-06-21-standards-mineadmin-alignment.md web/src/modules/education/api/standards web/src/modules/education/views/standards
git commit -m "完善标准化管理前端对齐"
```

---

Self-review: The plan covers standards API typing, placeholder page conversion, permission guards, tests, verification, and commit. No placeholder tasks remain, and the scope is limited to standards alignment.
