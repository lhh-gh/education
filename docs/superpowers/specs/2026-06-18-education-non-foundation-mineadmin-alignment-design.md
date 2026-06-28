# 教育非基础模块 MineAdmin 前端对齐设计

## 背景

教育后台已经完成基础设置模块的中文化与 MineAdmin 风格对齐，但其它业务模块仍保留早期手写页面。用户在 AI 助手的模型配置页看到 `AI Model Configs`、`Code`、`Provider`、`Save Feature`、`No AI model configs` 等英文文案，并在保存时遇到 `Permission denied`。这说明当前问题不只是单页文案，而是非基础模块缺少统一的 MineAdmin 前端页面规范、中文文案规范和按钮权限 seeder 对齐。

当前基础设置页面已经形成较稳定的参考模式：`mine-layout pt-3` 页面容器、卡片式内容区、中文查询表单、中文表格列、中文空状态、`useMessage` 反馈、`hasAuth` 控制按钮和菜单 seeder 绑定页面/按钮权限。非基础模块应沿用这套模式，避免继续出现“菜单中文、页面英文、接口无权限”的割裂体验。

## 目标

1. 基础设置之外的教育后台页面逐步按 MineAdmin 前端风格重构，优先解决用户正在使用的 AI 助手。
2. 所有用户可见文案中文化，包括标题、表单 label、按钮、表格列、状态、空状态、错误提示和成功提示。
3. 页面操作权限与后端接口权限一致，菜单 seeder 必须包含页面权限和对应按钮权限，并绑定到管理员/教育角色。
4. 保持路由 path、route name、组件 name、接口路径、权限码语义和业务字段稳定，避免破坏已有后端和菜单缓存。
5. 分批实施、分批测试、分批提交，降低一次性改动所有业务模块的风险。

## 范围

本设计覆盖 `web/src/modules/education/views` 下除 `foundation` 之外的业务模块：

- `ai`
- `academic`
- `operations`
- `admissions`
- `finance`
- `payroll`
- `group`
- `family`
- `workflow`
- `growth`
- `standards`
- `content`

第一批只实施 `ai` 模块，特别是截图暴露的问题页面：

- `AiModelConfigList.vue`
- `PromptTemplateList.vue`
- `GenerationTaskList.vue`
- `AiReviewList.vue`
- `RiskScoreList.vue`
- `DataQuestionWorkbench.vue`
- `AiRecommendationList.vue`
- `UsageDashboard.vue`
- `SafetyEventList.vue`
- `aiRules.ts`
- AI 相关前端测试
- `EducationMenuSeeder.php` 中 AI 菜单按钮权限
- AI 权限/菜单 seeder 测试

不在本阶段修改：

- 后端业务接口路径和数据库字段
- API 权限中间件整体机制
- 已完成的基础设置页面大范围返工
- uni-app 移动端页面
- 一次性重构所有教育业务模块

## 推荐方案：按模块批量对齐

采用分批改造策略。每批选择一个业务模块，统一完成页面结构、中文文案、权限按钮和测试，而不是只修单个英文字符串。

第一批为 AI 助手，原因是用户已经截图反馈，且该模块暴露了两类核心问题：页面英文和保存权限不足。AI 助手完成后，将其作为非基础模块模板，再按菜单顺序处理教务管理、运营中心、招生获客、财务中心、薪酬绩效、集团管控、家校服务、工作流中心、增长转化、标准化管理、内容教研。

## 备选方案

### 一次性改所有非基础模块

优点是能快速覆盖全部页面。缺点是页面数量多、权限动作多、测试面广，容易引入回归，也难以在单次提交中精确定位问题。

### 只修英文和 Permission denied

优点是最快解决当前截图。缺点是仍然保留早期手写页面和散落权限问题，后续其它菜单还会反复出现类似反馈。

## 页面规范

非基础模块页面应使用基础设置已验证的体验模式：

1. 页面根节点使用 `mine-layout pt-3` 和模块级 class，例如 `education-ai-page`。
2. 内容区域使用 MineAdmin 风格卡片容器，保持标题、工具栏、搜索区、表格区、分页区顺序一致。
3. 页面标题使用中文业务名，例如“模型配置”“提示词模板”“生成任务”“AI 审核”。
4. 查询区按钮统一为“查询 / 重置”，主操作为“新增 / 保存 / 发布 / 审核 / 处理”等中文。
5. 表单 label 和 placeholder 使用面向教育运营人员的中文，不展示后端字段名式英文；允许保留 `API Key`、`OpenID`、`UnionID`、`SLA`、`ROI` 等行业或技术缩写。
6. 状态值统一通过规则文件转换为中文，例如 `enabled -> 启用`、`disabled -> 停用`、`pending -> 待处理`、`approved -> 已通过`、`blocked -> 已阻断`。
7. 空状态使用中文，例如“暂无模型配置”“暂无提示词模板”。
8. 错误提示优先展示中文业务提示。后端返回英文 `Permission denied` 时，前端展示“暂无操作权限”，并通过权限 seeder 修复根因。
9. 操作按钮必须由 `hasAuth` 控制，不具备权限时不显示可触发接口的按钮。

## AI 助手第一批设计

### 模型配置页

`AiModelConfigList.vue` 改为中文 MineAdmin 风格列表页：

- 标题：“模型配置”
- 表单字段：“配置编码”“服务商”“模型名称”“API Key”“状态”
- 功能设置字段：“功能编码”“功能名称”“模型配置ID”“启用”“需要审核”“安全等级”
- 按钮：“保存模型”“保存功能”
- 表格列：“配置编码”“服务商”“模型名称”“密钥”“状态”
- 空状态：“暂无模型配置”
- 成功提示：“模型配置已保存”“功能设置已保存”
- 权限控制：保存模型按钮使用 `education:ai:model-config:save`，保存功能按钮使用 `education:ai:feature-setting:save`

### 其它 AI 页面

同一批中按现有页面逐个完成中文化和 MineAdmin 风格对齐：

- 提示词模板：模板编码、功能编码、模板名称、版本、发布状态、发布按钮
- 生成任务：任务编码、功能、业务类型、状态、创建时间
- AI 审核：生成结果、审核状态、安全状态、通过/拒绝/编辑
- 风险评分：学员、风险等级、风险分、建议动作
- 数据问答：问题、指标、答案、执行状态
- 智能推荐：推荐类型、对象、状态、采纳/忽略
- 用量统计：Token、费用、服务商、功能、日期范围
- 安全事件：风险等级、拦截原因、处理状态、处理按钮

## 权限与菜单 seeder 规范

`EducationMenuSeeder` 必须为页面实际调用的接口动作补齐按钮权限。AI 助手第一批至少包含：

- `education:ai:model-config:save`
- `education:ai:feature-setting:save`
- `education:ai:prompt:save`
- `education:ai:prompt:publish`
- `education:ai:generation:create`
- `education:ai:review:approve`
- `education:ai:review:handle`
- `education:ai:data-question:create`
- `education:ai:recommendation:adopt`
- `education:ai:safety:handle`

菜单 seeder 测试需要断言：

1. AI 页面菜单存在且中文标题正确。
2. AI 页面按钮权限存在，`meta.type = B`。
3. 管理员和教育管理角色绑定了 AI 页面与按钮权限。
4. 重跑 seeder 不会产生重复菜单。

## 数据流

页面加载流程：

1. Vue 页面进入后调用对应 `page*` API。
2. API client 保留现有接口路径和租户/校区 header。
3. 页面将响应列表渲染为中文表格。
4. 如果接口返回权限错误，页面显示中文权限提示。

保存流程：

1. 用户点击保存按钮。
2. 按钮先受 `hasAuth` 控制。
3. API client 调用后端保存接口。
4. 后端权限中间件校验当前用户是否拥有按钮权限。
5. seeder 绑定正确权限后，管理员/教育角色可正常保存。
6. 保存成功后页面展示中文成功提示并刷新列表。

## 测试策略

第一批 AI 助手需要先补测试，再实现：

1. 前端 Vitest：断言 AI 模型配置页渲染中文标题、表单 label、按钮、表格列和空状态。
2. 前端 Vitest：断言无保存权限时按钮不显示，后端权限错误映射为“暂无操作权限”。
3. API client 测试：保留现有接口路径和租户/校区 header 断言。
4. 后端 PHPUnit：断言 `EducationMenuSeeder` 创建 AI 保存/处理按钮权限并绑定教育管理角色。
5. 回归测试：运行 AI 相关前端测试、教育菜单 seeder 测试、前端 typecheck 和后端 analyse。

## 实施顺序

1. 完成并提交本设计文档。
2. 写 AI 助手第一批执行计划。
3. 为 AI 模型配置页和 AI 权限 seeder 增加失败测试。
4. 修复 AI 模型配置页中文化、按钮权限和权限错误文案。
5. 扩展同批其它 AI 页面中文化与 MineAdmin 风格对齐。
6. 运行 AI 前端测试、教育视图测试、typecheck、菜单 seeder 测试和后端 analyse。
7. 提交并推送 AI 助手第一批改造。
8. 按菜单顺序进入下一批模块。

## 验收标准

1. AI 助手页面不再出现用户可见英文文案；允许保留 `AI`、`API Key`、`Token`、`SLA`、`ROI` 等必要缩写。
2. AI 模型配置页保存按钮不再触发 `Permission denied`；无权限时按钮隐藏或显示中文权限提示。
3. AI 菜单页面标题、表单、表格、空状态、成功/错误提示都为中文。
4. AI 页面结构与基础设置页面保持一致的 MineAdmin 管理后台体验。
5. AI 页面实际调用的按钮权限被 seeder 写入 `menu` 表并绑定到管理员/教育角色。
6. 测试和类型检查通过，提交只包含当前批次相关文件，不提交 `.gitignore` 等无关改动。

## 风险与处理

- 风险：一次性改造 AI 全部页面仍然较多。处理：先完成模型配置页和权限 seeder，再推进同目录其它页面。
- 风险：权限码遗漏导致仍然 `Permission denied`。处理：从前端 API client 和后端路由双向列出动作权限，测试覆盖 seeder。
- 风险：误改接口字段或权限码导致后端不兼容。处理：只改用户可见文案和按钮权限绑定，不改接口字段名。
- 风险：英文技术缩写被过度翻译。处理：保留必要缩写，中文说明业务含义。
- 风险：非基础模块页面模式不一致。处理：以基础设置页面为基准抽取可复用中文规则和状态映射。

## 后续批次

AI 助手完成后，后续建议顺序：

1. 教务管理
2. 运营中心
3. 招生获客
4. 财务中心
5. 薪酬绩效
6. 集团管控
7. 家校服务
8. 工作流中心
9. 增长转化
10. 标准化管理
11. 内容教研

每批遵循同一规则：先补测试，后改页面和 seeder，验证通过后单独提交。
