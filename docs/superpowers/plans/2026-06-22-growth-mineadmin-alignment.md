# Growth MineAdmin Alignment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert the Education Growth pages into readable Chinese MineAdmin-style pages with consistent permission guards, real API usage, empty states, and loading states.

**Architecture:** Keep all work inside `web/src/modules/education/views/growth` and the existing `web/src/modules/education/api/growth` clients. Pages continue to use `mine-layout pt-3`, `el-card`, forms, tables, and existing permission guards through `hasAuth`.

**Tech Stack:** Vue 3 `<script setup>`, Element Plus, Vitest static alignment checks, existing MineAdmin `useHttp` API clients, and the current education router module.

---

### Task 1: Growth Alignment Tests

**Files:**
- Modify: `web/src/modules/education/views/growth/__tests__/GrowthWorkbench.spec.ts`
- Create: `web/src/modules/education/views/growth/__tests__/GrowthMineAdminAlignment.spec.ts`

- [ ] **Step 1: Write failing tests**

Add tests that assert growth route titles are readable Chinese, rule helpers return readable Chinese labels, and the eight target pages contain MineAdmin structure, Chinese titles, empty states, real API calls, loading states where data is fetched, and `hasAuth` guards for mutation buttons.

- [ ] **Step 2: Run tests to verify RED**

Run:

```bash
cd /home/phpgo/projects/www/education/web
source ~/.nvm/nvm.sh
npm test -- src/modules/education/views/growth
```

Expected: FAIL because `growthRules.ts`, `LeadScoreFactorDrawer.vue`, and several pages still contain broken copy or missing empty/loading states.

### Task 2: Rules and Shared Components

**Files:**
- Modify: `web/src/modules/education/views/growth/growthRules.ts`
- Modify: `web/src/modules/education/views/growth/components/AiTalkScriptEditor.vue`
- Modify: `web/src/modules/education/views/growth/components/FollowupSuggestionPanel.vue`
- Modify: `web/src/modules/education/views/growth/components/LeadScoreFactorDrawer.vue`

- [ ] **Step 1: Replace broken Chinese copy**

Update titles, labels, empty states, AI safety messages, suggestion action labels, and score-factor drawer columns to readable Chinese while preserving existing function names and payload shapes.

- [ ] **Step 2: Run growth tests**

Run:

```bash
cd /home/phpgo/projects/www/education/web
source ~/.nvm/nvm.sh
npm test -- src/modules/education/views/growth
```

Expected: Remaining failures now point only at page templates.

### Task 3: Growth Pages

**Files:**
- Modify: `web/src/modules/education/views/growth/GrowthWorkbench.vue`
- Modify: `web/src/modules/education/views/growth/LeadScoreList.vue`
- Modify: `web/src/modules/education/views/growth/AiTalkScriptWorkbench.vue`
- Modify: `web/src/modules/education/views/growth/FollowupStrategyList.vue`
- Modify: `web/src/modules/education/views/growth/TrialConversionList.vue`
- Modify: `web/src/modules/education/views/growth/ChannelRoiDashboard.vue`
- Modify: `web/src/modules/education/views/growth/ConsultantMetricDashboard.vue`
- Modify: `web/src/modules/education/views/growth/LossReasonReport.vue`

- [ ] **Step 1: Apply MineAdmin page pattern**

Use readable Chinese headers, search/save forms, loading tables, empty states, and permission notices. Keep dashboards as compact operational tables instead of decorative cards.

- [ ] **Step 2: Keep real API behavior**

Use the existing `getGrowthWorkbench`, `recalculateLeadScore`, `generateAiTalkScript`, `confirmAiTalkScript`, `saveFollowupStrategy`, `getTrialConversionLink`, `getChannelRoi`, `getConsultantMetrics`, `saveLossReason`, and `createLeadLossRecord` clients. Do not add fake backend calls or new routes in this batch.

- [ ] **Step 3: Run RED-to-GREEN growth tests**

Run:

```bash
cd /home/phpgo/projects/www/education/web
source ~/.nvm/nvm.sh
npm test -- src/modules/education/views/growth
```

Expected: PASS for growth tests.

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
git add docs/superpowers/plans/2026-06-22-growth-mineadmin-alignment.md web/src/modules/education/views/growth
git commit -m "完善增长转化前端对齐"
```

---

Self-review: The plan covers the growth frontend scope, includes RED/GREEN testing, avoids backend route expansion, and keeps the commit boundary limited to the growth frontend plus plan.
