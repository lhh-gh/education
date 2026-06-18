# 教育基础模块中文化与 MineAdmin 风格对齐设计

## 背景

教育后台菜单已经通过 seeder 改为中文，但基础模块页面仍有英文列表标题、表单标签、按钮、状态、空状态和错误提示，例如 `User profiles`、`New profile`、`Search`、`Reset`、`Enabled`。这会让教育 SaaS 后台在超级管理员视角下呈现出“菜单中文、页面英文”的割裂体验。

MineAdmin v3 前端基础页面通常使用 `mine-layout`、`mine-card`、`MaProTable`、`useTrans().globalTrans`、`useMessage`、`useDialog` 和统一 `crud.*` 文案组织列表、搜索、操作、弹窗与反馈。当前教育基础模块多为手写 `el-card + el-form + el-table`，需要先收敛用户可见文案，再逐步靠近 MineAdmin 的页面组织方式。

## 目标

1. 教育基础模块所有用户可见文案使用中文，页面内不再出现英文列表标题、表单标签、按钮、状态、空状态、弹窗标题和常见错误提示。
2. 复用 MineAdmin 通用体验：查询、重置、新增、编辑、删除、启用、停用、保存、取消、成功/失败提示优先使用 MineAdmin 既有中文表达。
3. 保持当前 API、权限码、路由 name、组件 name 和业务字段不变，避免影响后端权限、菜单缓存和测试。
4. 为后续 V1-V12 教育页面中文化留下可复用的文案映射与页面规范。

## 范围

本阶段只覆盖 `web/src/modules/education/views/foundation`：

- 列表页：`TenantList.vue`、`CampusList.vue`、`UserProfileList.vue`、`DictionaryList.vue`、`FeatureFlagList.vue`、`AuditLogList.vue`
- 表单/抽屉组件：`TenantForm.vue`、`CampusForm.vue`、`UserProfileForm.vue`、`CampusScopeForm.vue`、`DictTypeForm.vue`、`DictItemForm.vue`、`FeatureFlagForm.vue`、`AuditPayloadDrawer.vue`
- 规则文件：`actionRules.ts`、`auditLogRules.ts`、`components/auditPayloadRules.ts`
- 测试：基础模块现有 `__tests__` 中与文案、权限、状态展示相关的用例

不在本阶段处理：

- V1-V12 其他业务模块页面的大规模中文化
- 后端接口字段名、枚举值、权限码、菜单 `name` 的变更
- 将所有页面一次性重构为 `MaProTable` 的大改造
- 新增多语言资源体系；本阶段允许教育模块先使用本地中文常量，后续再抽到 i18n

## 方案

### 推荐方案：先中文化，再轻量对齐 MineAdmin

先把基础模块页面和组件中的英文文案替换为中文，并抽出基础模块共用的角色、状态、动作、字段文案映射；同时把页面容器和通用操作文案向 MineAdmin 靠拢。这样改动小、风险低，能快速解决用户当前看到的英文问题。

具体做法：

1. 建立基础模块本地文案映射，例如角色 `platform_super_admin -> 平台超级管理员`、状态 `enabled -> 启用`、`disabled -> 停用`。
2. 列表页统一中文标题、查询项、表格列、按钮、空状态、弹窗标题和错误提示。
3. 表单组件统一中文字段标签、占位符、校验提示、保存成功提示。
4. 页面容器保留现有结构，但统一使用 MineAdmin 常见的 `mine-layout pt-3`、`mine-card` 或等价页面样式；通用按钮文案优先对齐 `crud.search`、`crud.reset`、`crud.add` 等语义。
5. 保留现有 `el-table` 实现，只有在某个页面改动范围可控时再局部引入 `MaProTable`。全量 ProTable 化放到下一阶段。

### 备选方案：一次性 ProTable 化

把 6 个基础列表页全部重构为 `MaProTable` schema 模式，并同步迁移搜索项、表格列、工具栏和弹窗。优点是 MineAdmin 风格最彻底；缺点是改动大，容易影响现有测试和业务行为，不适合作为当前修复第一步。

### 备选方案：只替换硬编码英文

只做字符串替换，不调整页面结构和文案映射。优点是最快；缺点是重复文案会继续散落在页面里，后续 V1-V12 还会反复出现同类问题。

## 文案规范

基础模块采用面向校区/机构运营人员的中文名称：

- Tenant：机构
- Campus：校区
- User Profile：教育用户档案
- Campus Scope：校区范围
- Dictionary：数据字典
- Dict Type：字典类型
- Dict Item：字典项
- Feature Flag：功能开关
- Audit Log：审计日志
- Audit Payload：审计详情
- Platform Super Admin：平台超级管理员
- Platform Operator：平台运营
- Tenant Admin：机构管理员
- Principal：校长
- Academic Staff：教务
- Front Desk：前台
- Teacher：教师
- Finance：财务
- Guardian：家长

通用动作：

- Search：查询
- Reset：重置
- New/Create：新增
- Edit：编辑
- Delete：删除
- Enable：启用
- Disable：停用
- Save：保存
- Cancel：取消
- Scope：范围
- Detail：详情

状态：

- Enabled：启用
- Disabled：停用
- Active：生效
- Inactive：未生效

## MineAdmin 风格对齐规则

1. 页面根容器使用 `mine-layout pt-3`，内容区域使用 MineAdmin 风格卡片容器，避免页面风格与系统模块割裂。
2. 列表页保留权限判断 `hasAuth`，按钮出现规则不变。
3. 通用消息继续使用 `useMessage`，确认删除、保存成功、加载失败等提示改为中文。
4. 搜索区按钮文案保持“查询 / 重置”；分页、表格加载、空状态保持 Element Plus 与 MineAdmin 的常见交互。
5. 字段 label 不展示后端字段名式英文；必要时保留技术标识为“机构ID”“校区ID”“OpenID”“UnionID”等。
6. `defineOptions({ name })`、接口导入、权限码、路由路径不做中文化，避免破坏 KeepAlive、权限与菜单匹配。

## 实施顺序

1. 新增或整理基础模块本地文案映射，优先复用现有 `actionRules.ts` 一类文件，避免每个页面重复角色/状态翻译。
2. 修复 `UserProfileList.vue` 与 `UserProfileForm.vue`，因为当前英文最明显，也是用户截图里的核心问题。
3. 修复 `CampusScopeForm.vue` 和审计详情抽屉，保证用户档案相关弹窗也完整中文化。
4. 扫描并修复其余基础模块页面和表单中的英文文案。
5. 更新或补充 Vitest 用例，至少覆盖角色中文、状态中文、查询按钮中文、空状态中文。
6. 运行前端测试与类型检查，失败必须修复。

## 验收标准

1. `web/src/modules/education/views/foundation` 下用户可见英文文案被替换为中文；允许保留 `OpenID`、`UnionID`、API 字段名、权限码、组件名、类型名。
2. 教育用户档案列表展示“教育用户档案”“新增档案”“机构ID”“关键字”“角色”“状态”“查询”“重置”“平台超级管理员”等中文。
3. 教育用户档案表单和校区范围弹窗不再出现 `New profile`、`Edit profile`、`Campus scope`、`Enabled`、`Disabled` 等英文。
4. 现有基础模块权限控制、分页、搜索、状态切换、删除、保存行为不变。
5. `pnpm test` 中基础模块相关测试通过；若仓库已有更精确命令，优先运行基础模块测试和现有教育视图测试。
6. 提交只包含本阶段前端基础模块文案/样式对齐和对应测试，不提交无关文件。

## 风险与处理

- 风险：中文化时误改权限码或枚举值。处理：只改 label、placeholder、message、title 和展示函数，不改 value。
- 风险：一次性 ProTable 化引入行为回归。处理：本阶段不做全量 ProTable 重构，只做轻量风格对齐。
- 风险：重复中文映射散落。处理：先抽基础模块本地常量，后续 V1-V12 可复用或迁移到 i18n。
- 风险：控制台乱码影响判断。处理：以文件内容和浏览器实际显示为准，命令行输出中的 mojibake 不作为中文损坏依据。

## 后续计划

本设计完成并通过用户确认后，再写执行计划，按基础模块页面顺序实现并逐步提交。基础模块完成后，再为 V1-V12 页面建立批量中文化与 MineAdmin ProTable 对齐计划。
