# Content MineAdmin Alignment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert the remaining Education Content pages into Chinese MineAdmin-style pages with consistent permission guards, real API usage, empty states, and pagination.

**Architecture:** Keep all work inside `web/src/modules/education/views/content` and the existing `web/src/modules/education/api/content` clients. Pages use the existing `mine-layout pt-3` + `el-card` + form/table/pagination structure, and mutation buttons stay behind `hasAuth`.

**Tech Stack:** Vue 3 `<script setup>`, Element Plus, Vitest static alignment checks, existing MineAdmin `useHttp` API clients, and current education scope request helpers.

---

### Task 1: Content Alignment Tests

**Files:**
- Modify: `web/src/modules/education/views/content/__tests__/LearningMaterialList.spec.ts`
- Create: `web/src/modules/education/views/content/__tests__/ContentMineAdminAlignment.spec.ts`

- [ ] **Step 1: Write failing tests**

Add tests that assert content route titles are Chinese, rule helpers return Chinese labels, and the six target pages contain MineAdmin structure, Chinese titles, Chinese empty states, real API calls, and `hasAuth` guards for mutation buttons.

- [ ] **Step 2: Run tests to verify RED**

Run:

```bash
cd /home/phpgo/projects/www/education/web
source ~/.nvm/nvm.sh
npm test -- src/modules/education/views/content
```

Expected: FAIL because several content pages and rule helpers still contain old or broken copy.

### Task 2: Rules and Shared Components

**Files:**
- Modify: `web/src/modules/education/views/content/contentRules.ts`
- Modify: `web/src/modules/education/views/content/components/MaterialVersionDrawer.vue`
- Modify: `web/src/modules/education/views/content/components/ShowcaseEditor.vue`
- Modify: `web/src/modules/education/views/content/components/LearningMaterialForm.vue`

- [ ] **Step 1: Replace broken Chinese copy**

Update labels, placeholders, status badges, permission notices, and edit-state badges to readable Chinese while preserving existing function names and payload shapes.

- [ ] **Step 2: Run content tests**

Run:

```bash
cd /home/phpgo/projects/www/education/web
source ~/.nvm/nvm.sh
npm test -- src/modules/education/views/content
```

Expected: Remaining failures now point only at page templates.

### Task 3: Content Pages

**Files:**
- Modify: `web/src/modules/education/views/content/MaterialVersionList.vue`
- Modify: `web/src/modules/education/views/content/MaterialRelationEditor.vue`
- Modify: `web/src/modules/education/views/content/StudentWorkList.vue`
- Modify: `web/src/modules/education/views/content/ContentReviewList.vue`
- Modify: `web/src/modules/education/views/content/MaterialUsageDashboard.vue`
- Modify: `web/src/modules/education/views/content/ShowcaseList.vue`

- [ ] **Step 1: Apply MineAdmin page pattern**

Use readable Chinese header, search/save forms, loading table, empty state, and total pagination where the page endpoint returns totals.

- [ ] **Step 2: Keep real API behavior**

Use the existing `page*`, `save*`, `publish*`, `withdraw*`, and `review*` clients. Do not add fake backend calls or new routes in this batch.

- [ ] **Step 3: Run RED-to-GREEN content tests**

Run:

```bash
cd /home/phpgo/projects/www/education/web
source ~/.nvm/nvm.sh
npm test -- src/modules/education/views/content
```

Expected: PASS for content tests.

### Task 4: Verification and Commit

**Files:**
- All files changed above.

- [ ] **Step 1: Run frontend checks**

```bash
cd /home/phpgo/projects/www/education/web
source ~/.nvm/nvm.sh
npm test -- src/modules/education
npx eslint src/modules/education --quiet
npx stylelint "src/modules/education/**/*.{css,scss,vue}" --quiet
npm run typecheck
```

- [ ] **Step 2: Run backend static analysis**

```bash
cd /home/phpgo/projects/www/education
composer analyse
```

- [ ] **Step 3: Commit in Chinese**

```bash
git add docs/superpowers/plans/2026-06-21-content-mineadmin-alignment.md web/src/modules/education/views/content
git commit -m "完善内容教研前端对齐"
```

---

Self-review: The plan covers the approved content module scope, includes RED/GREEN testing, avoids backend route expansion, and keeps the commit boundary limited to the content frontend plus plan.
