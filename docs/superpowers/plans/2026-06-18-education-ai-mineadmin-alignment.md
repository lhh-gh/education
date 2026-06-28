# Education AI MineAdmin Alignment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Align the first non-foundation education module, AI Assistant, with the MineAdmin-style Chinese admin experience and fix missing AI action menu permissions.

**Architecture:** Keep existing AI API clients, route names, route paths, and component names stable. Move AI display labels, status translation, permission helpers, and permission-error mapping into `aiRules.ts`, then let AI pages consume those helpers. Extend `EducationMenuSeeder` button permissions so frontend actions match backend permission checks.

**Tech Stack:** MineAdmin Vue 3, Element Plus, Vitest, Hyperf/PHPUnit, `EducationMenuSeeder`.

---

### Task 1: AI Rules And Model Config Page

**Files:**
- Modify: `web/src/modules/education/views/ai/aiRules.ts`
- Modify: `web/src/modules/education/views/ai/AiModelConfigList.vue`
- Test: `web/src/modules/education/views/ai/__tests__/AiModelConfigList.spec.ts`

- [x] **Step 1: Write failing tests**

Create tests that assert:

```ts
expect(aiErrorTitle(403)).toBe('暂无操作权限')
expect(aiStatusLabel('enabled')).toBe('启用')
expect(aiStatusLabel('disabled')).toBe('停用')
expect(aiFeatureSafetyLevelLabel('normal')).toBe('普通')
expect(aiModelConfigActionsByPermission([]).canSaveModel).toBe(false)
expect(aiModelConfigActionsByPermission(['education:ai:model-config:save']).canSaveModel).toBe(true)
expect(aiModelConfigText.title).toBe('模型配置')
expect(aiModelConfigText.empty).toBe('暂无模型配置')
```

- [x] **Step 2: Verify red**

Run:

```bash
cd web && pnpm test src/modules/education/views/ai/__tests__/AiModelConfigList.spec.ts
```

Expected: fails because the Chinese helpers and permission helper do not exist yet, and `aiErrorTitle(403)` still returns English.

- [x] **Step 3: Implement rules and page consumption**

Add Chinese text constants, status/safety labels, and permission helper in `aiRules.ts`. Update `AiModelConfigList.vue` to use Chinese labels, `hasAuth`, `useMessage`, Chinese success/error messages, `aiStatusLabel`, and hidden save buttons when permission is missing.

- [x] **Step 4: Verify green**

Run:

```bash
cd web && pnpm test src/modules/education/views/ai/__tests__/AiModelConfigList.spec.ts
```

Expected: passes.

### Task 2: AI Menu Seeder Permissions

**Files:**
- Modify: `databases/seeders/EducationMenuSeeder.php`
- Test: `tests/Feature/Education/Foundation/EducationMenuSeederTest.php`

- [x] **Step 1: Write failing seeder assertions**

Extend the existing seeder test to assert these AI buttons exist with `meta.type = B` and are bound to `education_tenant_admin`:

```php
$expectedAiButtons = [
    'education:ai:model-config:save',
    'education:ai:feature-setting:save',
    'education:ai:prompt:save',
    'education:ai:prompt:publish',
    'education:ai:generation:create',
    'education:ai:review:approve',
    'education:ai:review:handle',
    'education:ai:data-question:create',
    'education:ai:recommendation:adopt',
    'education:ai:safety:handle',
];
```

- [x] **Step 2: Verify red**

Run:

```bash
composer test -- --filter EducationMenuSeederTest
```

Expected: fails because several AI buttons are not created.

- [x] **Step 3: Add missing AI button permissions**

Update AI page button arrays in `EducationMenuSeeder.php` without changing routes, components, or page permission names.

- [x] **Step 4: Verify green**

Run:

```bash
composer test -- --filter EducationMenuSeederTest
```

Expected: passes.

### Task 3: Verification And Commit

**Files:**
- Modify only files touched by Tasks 1 and 2 plus this plan.

- [x] **Step 1: Run frontend AI tests**

```bash
cd web && pnpm test src/modules/education/views/ai/__tests__
```

- [x] **Step 2: Run frontend typecheck**

```bash
cd web && pnpm typecheck
```

- [x] **Step 3: Run backend menu test and analyse**

```bash
composer test -- --filter EducationMenuSeederTest
composer analyse
```

- [x] **Step 4: Inspect git status**

```bash
git status --short
```

Expected: only `.gitignore` is unstaged user work; staged files must be AI alignment files, the menu seeder test, and this plan.

- [ ] **Step 5: Commit and push**

```bash
git add docs/superpowers/plans/2026-06-18-education-ai-mineadmin-alignment.md \
  web/src/modules/education/views/ai/aiRules.ts \
  web/src/modules/education/views/ai/AiModelConfigList.vue \
  web/src/modules/education/views/ai/__tests__/AiModelConfigList.spec.ts \
  databases/seeders/EducationMenuSeeder.php \
  tests/Feature/Education/Foundation/EducationMenuSeederTest.php
git commit -m "fix(education): 对齐 AI 助手后台页面"
git push origin f00-environment
```
