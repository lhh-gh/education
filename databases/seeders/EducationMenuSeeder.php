<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
use App\Model\Enums\User\Status;
use App\Model\Permission\Menu;
use App\Model\Permission\Meta;
use App\Model\Permission\Role;
use Hyperf\Database\Seeders\Seeder;
use Hyperf\DbConnection\Db;

class EducationMenuSeeder extends Seeder
{
    private const BASE_DATA = [
        'path' => '',
        'component' => '',
        'redirect' => '',
        'status' => 1,
        'sort' => 0,
        'created_by' => 0,
        'updated_by' => 0,
        'remark' => 'Education menu',
    ];

    private const ROLE_CODES = [
        'SuperAdmin',
        'superAdmin',
        'admin',
        'administrator',
        'education_platform_operator',
        'education_tenant_admin',
        'education_principal',
        'education_academic_staff',
        'education_front_desk',
        'education_teacher',
        'education_finance',
    ];

    private const MENU_TITLES = [
        'education' => '教育管理',
        'education:foundation' => '基础设置',
        'education:foundation:tenant:page' => '机构管理',
        'education:foundation:campus:page' => '校区管理',
        'education:foundation:user-profile:page' => '用户档案',
        'education:foundation:dictionary:page' => '数据字典',
        'education:foundation:feature-flag:page' => '功能开关',
        'education:foundation:audit-log:page' => '审计日志',
        'education:academic' => '教务管理',
        'education:academic:report:dashboard' => '教务看板',
        'education:academic:classroom:page' => '教室管理',
        'education:academic:student:page' => '学员管理',
        'education:academic:guardian:page' => '家长管理',
        'education:academic:teacher:page' => '教师管理',
        'education:academic:course:page' => '课程管理',
        'education:academic:lesson-package:page' => '课包管理',
        'education:academic:enrollment:page' => '报名管理',
        'education:academic:student-course-account:page' => '课时账户',
        'education:academic:class:page' => '班级管理',
        'education:academic:lesson-schedule:calendar' => '排课日历',
        'education:academic:lesson:page' => '课次管理',
        'education:academic:attendance:lesson-page' => '考勤复核',
        'education:academic:leave-request:page' => '请假申请',
        'education:academic:lesson-change:page' => '调课记录',
        'education:academic:consumption:page' => '消课流水',
        'education:academic:account-adjustment:page' => '账户调整',
        'education:academic:notice:page' => '通知公告',
        'education:academic:report:attendance' => '考勤报表',
        'education:academic:report:consumption' => '消课报表',
        'education:academic:report:account-balance' => '课时余额报表',
        'education:academic:report:leave' => '请假报表',
        'education:academic:report:acceptance' => '验收报告',
        'education:operations' => '运营中心',
        'education:operations:lesson-change:page' => '调课中心',
        'education:operations:makeup:page' => '补课闭环',
        'education:operations:consumption-review:page' => '消课审核',
        'education:operations:renewal-alert:page' => '续费提醒',
        'education:operations:teacher-workload:report' => '教师工作量',
        'education:operations:dashboard:overview' => '运营看板',
        'education:admissions' => '招生获客',
        'education:admissions:lead-source:page' => '线索来源',
        'education:admissions:lead:page' => '线索池',
        'education:admissions:lead:detail' => '线索详情',
        'education:admissions:trial:page' => '试听日历',
        'education:admissions:trial-feedback:page' => '试听反馈',
        'education:admissions:conversion:page' => '线索转化',
        'education:admissions:task:page' => '招生任务',
        'education:admissions:dashboard:overview' => '招生活动看板',
        'education:finance' => '财务中心',
        'education:finance:dashboard:overview' => '财务看板',
        'education:finance:order:page' => '订单管理',
        'education:finance:payment:page' => '收款记录',
        'education:finance:payment-channel:page' => '支付渠道',
        'education:finance:refund:page' => '退费管理',
        'education:finance:receipt:page' => '票据管理',
        'education:finance:reconciliation:page' => '对账管理',
        'education:payroll' => '薪酬绩效',
        'education:payroll:rule:page' => '薪酬规则',
        'education:payroll:batch:page' => '薪酬批次',
        'education:payroll:slip:page' => '工资条',
        'education:payroll:review:page' => '薪酬复核',
        'education:payroll:payment:page' => '薪酬发放',
        'education:payroll:dispute:page' => '工作量申诉',
        'education:payroll:performance:page' => '教师绩效',
        'education:group' => '集团管控',
        'education:group:metric:page' => '集团看板',
        'education:group:org:tree' => '组织架构',
        'education:group:data-permission:page' => '数据权限',
        'education:group:approval-template:page' => '审批模板',
        'education:group:approval-task:page' => '审批任务',
        'education:group:contract:page' => '合同管理',
        'education:group:contract-renewal:page' => '合同续签',
        'education:group:franchise:page' => '加盟管理',
        'education:group:risk-audit:page' => '风控审计',
        'education:family' => '家校服务',
        'education:family:comment-template:page' => '评语模板',
        'education:family:performance-tag:page' => '表现标签',
        'education:family:homework:page' => '课后作业',
        'education:family:report:page' => '学习报告',
        'education:family:growth:page' => '成长记录',
        'education:family:message:page' => '家校消息',
        'education:family:quality:page' => '服务质量',
        'education:ai' => 'AI 助手',
        'education:ai:model-config:page' => '模型配置',
        'education:ai:prompt:page' => '提示词模板',
        'education:ai:generation:page' => '生成任务',
        'education:ai:review:page' => 'AI 审核',
        'education:ai:risk-score:page' => '风险评分',
        'education:ai:data-question:page' => '数据问答',
        'education:ai:data-question:create' => '数据问答',
        'education:ai:recommendation:page' => '智能推荐',
        'education:ai:usage:summary' => '用量统计',
        'education:ai:safety:page' => '安全事件',
        'education:workflow' => '工作流中心',
        'education:workflow:rule:page' => '流程规则',
        'education:workflow:task:page' => '流程任务',
        'education:workflow:alert:page' => '预警中心',
        'education:workflow:sla:page' => 'SLA 策略',
        'education:workflow:escalation:page' => '升级策略',
        'education:workflow:template:page' => '流程模板',
        'education:workflow:metric:page' => '流程指标',
        'education:growth' => '增长转化',
        'education:growth:workbench:view' => '增长工作台',
        'education:growth:score:recalculate' => '线索评分',
        'education:growth:ai-script:generate' => 'AI 话术',
        'education:growth:strategy:save' => '跟进策略',
        'education:growth:trial-conversion:view' => '试听转化',
        'education:growth:channel-roi:page' => '渠道 ROI',
        'education:growth:consultant-metric:page' => '顾问指标',
        'education:growth:loss:create' => '流失原因',
        'education:standards' => '标准化管理',
        'education:standards:package:page' => '服务包',
        'education:standards:stage-goal:page' => '阶段目标',
        'education:standards:ability:page' => '能力点',
        'education:standards:trial:page' => '试听标准',
        'education:standards:delivery:page' => '交付标准',
        'education:standards:template:page' => '服务模板',
        'education:standards:material:page' => '课程资料',
        'education:standards:quality:page' => '课程反馈',
        'education:standards:quality-dashboard:page' => '质量看板',
        'education:standards:version:page' => '标准版本',
        'education:standards:review:page' => '标准评审',
        'education:content' => '内容教研',
        'education:content:material:page' => '学习资料',
        'education:content:version:page' => '资料版本',
        'education:content:attachment:page' => '资料附件',
        'education:content:relation:page' => '资源关联',
        'education:content:student-work:page' => '学员作品',
        'education:content:showcase:page' => '作品展示',
        'education:content:review:page' => '内容审核',
        'education:content:metric:page' => '使用指标',
    ];

    private const ACTION_TITLES = [
        'adopt' => '采纳',
        'adjust' => '调整',
        'approve' => '通过',
        'assign' => '分配',
        'attendance' => '考勤',
        'bind-course' => '绑定课程',
        'bind-student' => '绑定学员',
        'calculate' => '计算',
        'cancel' => '取消',
        'confirm' => '确认',
        'convert' => '转化',
        'create' => '新增',
        'delete' => '删除',
        'detail' => '详情',
        'follow' => '跟进',
        'generate' => '生成',
        'handle' => '处理',
        'page' => '列表',
        'publish' => '发布',
        'recalculate' => '重新计算',
        'reply' => '回复',
        'review' => '审核',
        'save' => '保存',
        'status' => '状态',
        'summary' => '统计',
        'sync' => '同步',
        'toggle' => '切换',
        'update' => '编辑',
        'upload' => '上传',
        'view' => '查看',
        'withdraw' => '撤回',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->removeExistingEducationMenus();
        $this->create($this->menus());
        $this->bindMenusToRoles();
    }

    private function removeExistingEducationMenus(): void
    {
        $ids = Menu::query()
            ->where('name', 'education')
            ->orWhere('name', 'like', 'education:%')
            ->pluck('id')
            ->all();

        if ($ids === []) {
            return;
        }

        Db::table('role_belongs_menu')->whereIn('menu_id', $ids)->delete();
        Menu::query()->whereIn('id', $ids)->delete();
    }

    private function create(array $data, int $parentId = 0): void
    {
        foreach ($data as $sort => $item) {
            $children = $item['children'] ?? [];
            unset($item['children']);

            $menu = Menu::query()->create(array_merge(self::BASE_DATA, $item, [
                'parent_id' => $parentId,
                'sort' => ($sort + 1) * 10,
            ]));

            if ($children !== []) {
                $this->create($children, (int) $menu->id);
            }
        }
    }

    private function bindMenusToRoles(): void
    {
        $menuIds = Menu::query()
            ->where('name', 'education')
            ->orWhere('name', 'like', 'education:%')
            ->pluck('id')
            ->all();

        if ($menuIds === []) {
            return;
        }

        Role::query()
            ->whereIn('code', self::ROLE_CODES)
            ->where('status', Status::Normal)
            ->each(static function (Role $role) use ($menuIds): void {
                $role->menus()->syncWithoutDetaching($menuIds);
            });
    }

    private function menus(): array
    {
        return [
            $this->menu('education', '教育管理', '/education', '', 'material-symbols:school-outline-rounded', [
                'redirect' => '/education/foundation/tenants',
                'children' => [
                    $this->group('education:foundation', '基础设置', '/education/foundation', '/education/foundation/tenants', 'material-symbols:settings-outline-rounded', [
                        $this->page('education:foundation:tenant:page', 'Tenants', '/education/foundation/tenants', 'education/views/foundation/TenantList', ['education:foundation:tenant:save', 'education:foundation:tenant:update', 'education:foundation:tenant:delete']),
                        $this->page('education:foundation:campus:page', 'Campuses', '/education/foundation/campuses', 'education/views/foundation/CampusList', ['education:foundation:campus:save', 'education:foundation:campus:update', 'education:foundation:campus:delete']),
                        $this->page('education:foundation:user-profile:page', 'User Profiles', '/education/foundation/user-profiles', 'education/views/foundation/UserProfileList', ['education:foundation:user-profile:save', 'education:foundation:user-profile:update', 'education:foundation:user-profile:delete']),
                        $this->page('education:foundation:dictionary:page', 'Dictionaries', '/education/foundation/dictionaries', 'education/views/foundation/DictionaryList', ['education:foundation:dictionary:save', 'education:foundation:dictionary:update', 'education:foundation:dictionary:delete']),
                        $this->page('education:foundation:feature-flag:page', 'Feature Flags', '/education/foundation/feature-flags', 'education/views/foundation/FeatureFlagList', ['education:foundation:feature-flag:save', 'education:foundation:feature-flag:update', 'education:foundation:feature-flag:toggle']),
                        $this->page('education:foundation:audit-log:page', 'Audit Logs', '/education/foundation/audit-logs', 'education/views/foundation/AuditLogList', ['education:foundation:audit-log:detail']),
                    ]),
                    $this->group('education:academic', '教务管理', '/education/academic', '/education/academic/dashboard', 'material-symbols:auto-stories-outline-rounded', [
                        $this->page('education:academic:report:dashboard', 'Academic Dashboard', '/education/academic/dashboard', 'education/views/academic/AcademicDashboard'),
                        $this->page('education:academic:classroom:page', 'Classrooms', '/education/academic/classrooms', 'education/views/academic/ClassroomList', ['education:academic:classroom:save', 'education:academic:classroom:update', 'education:academic:classroom:delete']),
                        $this->page('education:academic:student:page', 'Students', '/education/academic/students', 'education/views/academic/StudentList', ['education:academic:student:save', 'education:academic:student:update', 'education:academic:student:delete']),
                        $this->page('education:academic:guardian:page', 'Guardians', '/education/academic/guardians', 'education/views/academic/GuardianList', ['education:academic:guardian:save', 'education:academic:guardian:update', 'education:academic:guardian:bind-student']),
                        $this->page('education:academic:teacher:page', 'Teachers', '/education/academic/teachers', 'education/views/academic/TeacherList', ['education:academic:teacher:save', 'education:academic:teacher:update', 'education:academic:teacher:bind-course']),
                        $this->page('education:academic:course:page', 'Courses', '/education/academic/courses', 'education/views/academic/CourseList', ['education:academic:course:save', 'education:academic:course:update', 'education:academic:course:delete']),
                        $this->page('education:academic:lesson-package:page', 'Lesson Packages', '/education/academic/lesson-packages', 'education/views/academic/LessonPackageList', ['education:academic:lesson-package:save', 'education:academic:lesson-package:update']),
                        $this->page('education:academic:enrollment:page', 'Enrollments', '/education/academic/enrollments', 'education/views/academic/EnrollmentWorkbench', ['education:academic:enrollment:save', 'education:academic:enrollment:confirm']),
                        $this->page('education:academic:student-course-account:page', 'Course Accounts', '/education/academic/course-accounts', 'education/views/academic/AccountLedgerList', ['education:academic:student-course-account:adjust']),
                        $this->page('education:academic:class:page', 'Classes', '/education/academic/classes', 'education/views/academic/ClassList', ['education:academic:class:save', 'education:academic:class:update']),
                        $this->page('education:academic:lesson-schedule:calendar', 'Lesson Schedule', '/education/academic/lesson-schedule', 'education/views/academic/LessonScheduleCalendar', ['education:academic:lesson-schedule:save']),
                        $this->page('education:academic:lesson:page', 'Lessons', '/education/academic/lessons', 'education/views/academic/LessonList', ['education:academic:lesson:save', 'education:academic:lesson:update', 'education:academic:lesson:cancel']),
                        $this->page('education:academic:attendance:lesson-page', 'Attendance Review', '/education/academic/attendance-review', 'education/views/academic/AttendanceReview', ['education:academic:attendance:confirm']),
                        $this->page('education:academic:leave-request:page', 'Leave Requests', '/education/academic/leave-requests', 'education/views/academic/LeaveRequestList', ['education:academic:leave-request:review']),
                        $this->page('education:academic:lesson-change:page', 'Lesson Changes', '/education/academic/lesson-changes', 'education/views/academic/LessonChangeList', ['education:academic:lesson-change:review']),
                        $this->page('education:academic:consumption:page', 'Consumption Ledger', '/education/academic/consumptions', 'education/views/academic/ConsumptionLedgerList'),
                        $this->page('education:academic:account-adjustment:page', 'Account Adjustments', '/education/academic/account-adjustments', 'education/views/academic/AccountAdjustmentList', ['education:academic:account-adjustment:save']),
                        $this->page('education:academic:notice:page', 'Notices', '/education/academic/notices', 'education/views/academic/NoticeList', ['education:academic:notice:publish']),
                        $this->page('education:academic:report:attendance', 'Attendance Report', '/education/academic/reports/attendance', 'education/views/academic/AttendanceReport'),
                        $this->page('education:academic:report:consumption', 'Consumption Report', '/education/academic/reports/consumption', 'education/views/academic/ConsumptionReport'),
                        $this->page('education:academic:report:account-balance', 'Account Balance Report', '/education/academic/reports/account-balances', 'education/views/academic/AccountBalanceReport'),
                        $this->page('education:academic:report:leave', 'Leave Report', '/education/academic/reports/leaves', 'education/views/academic/LeaveReport'),
                        $this->page('education:academic:report:acceptance', '验收报告', '/education/academic/reports/v1-acceptance', 'education/views/academic/V1AcceptanceReport'),
                    ]),
                    $this->group('education:operations', '运营中心', '/education/operations', '/education/operations/lesson-changes', 'material-symbols:fact-check-outline-rounded', [
                        $this->page('education:operations:lesson-change:page', 'Lesson Change Center', '/education/operations/lesson-changes', 'education/views/operations/LessonChangeCenter', ['education:operations:lesson-change:review']),
                        $this->page('education:operations:makeup:page', 'Make-up Closure', '/education/operations/makeups', 'education/views/operations/LeaveMakeupList', ['education:operations:makeup:save']),
                        $this->page('education:operations:consumption-review:page', 'Consumption Review', '/education/operations/consumption-reviews', 'education/views/operations/ConsumptionReviewList', ['education:operations:consumption-review:review']),
                        $this->page('education:operations:renewal-alert:page', 'Renewal Alerts', '/education/operations/renewal-alerts', 'education/views/operations/RenewalAlertList', ['education:operations:renewal-alert:follow']),
                        $this->page('education:operations:teacher-workload:report', 'Teacher Workloads', '/education/operations/teacher-workloads', 'education/views/operations/TeacherWorkloadReport'),
                        $this->page('education:operations:dashboard:overview', 'Operation Dashboard', '/education/operations/dashboard', 'education/views/operations/OperationDashboard'),
                    ]),
                    $this->group('education:admissions', '招生获客', '/education/admissions', '/education/admissions/leads', 'material-symbols:person-search-outline-rounded', [
                        $this->page('education:admissions:lead-source:page', '线索来源', '/education/admissions/lead-sources', 'education/views/admissions/LeadSourceList', ['education:admissions:lead-source:create']),
                        $this->page('education:admissions:lead:page', '线索池', '/education/admissions/leads', 'education/views/admissions/LeadPool', ['education:admissions:lead:create', 'education:admissions:lead:assign']),
                        $this->page('education:admissions:lead:detail', '线索详情', '/education/admissions/leads/:id', 'education/views/admissions/LeadDetail', ['education:admissions:lead:follow']),
                        $this->page('education:admissions:trial:page', '试听日历', '/education/admissions/trials', 'education/views/admissions/TrialLessonCalendar', ['education:admissions:trial:create', 'education:admissions:trial:attendance']),
                        $this->page('education:admissions:trial-feedback:page', '试听反馈', '/education/admissions/trial-feedbacks', 'education/views/admissions/TrialFeedbackList', ['education:admissions:trial-feedback:create']),
                        $this->page('education:admissions:conversion:page', '线索转化', '/education/admissions/conversion', 'education/views/admissions/LeadConversionWorkbench', ['education:admissions:lead:convert']),
                        $this->page('education:admissions:task:page', '招生任务', '/education/admissions/tasks', 'education/views/admissions/AdmissionTaskList'),
                        $this->page('education:admissions:dashboard:overview', '招生看板', '/education/admissions/dashboard', 'education/views/admissions/AdmissionDashboard'),
                    ]),
                    $this->group('education:finance', '财务中心', '/education/finance', '/education/finance/dashboard', 'material-symbols:payments-outline-rounded', [
                        $this->page('education:finance:dashboard:overview', 'Finance Dashboard', '/education/finance/dashboard', 'education/views/finance/FinanceDashboard'),
                        $this->page('education:finance:order:page', 'Orders', '/education/finance/orders', 'education/views/finance/FinanceOrderList', ['education:finance:order:save', 'education:finance:order:confirm']),
                        $this->page('education:finance:payment:page', 'Payments', '/education/finance/payments', 'education/views/finance/PaymentRecordList', ['education:finance:payment:confirm']),
                        $this->page('education:finance:payment-channel:page', 'Payment Channels', '/education/finance/channels', 'education/views/finance/PaymentChannelList', ['education:finance:payment-channel:save']),
                        $this->page('education:finance:refund:page', 'Refunds', '/education/finance/refunds', 'education/views/finance/RefundRequestList', ['education:finance:refund:review']),
                        $this->page('education:finance:receipt:page', 'Receipts', '/education/finance/receipts', 'education/views/finance/ReceiptList', ['education:finance:receipt:issue']),
                        $this->page('education:finance:reconciliation:page', 'Reconciliation', '/education/finance/reconciliation', 'education/views/finance/ReconciliationBatchList', ['education:finance:reconciliation:save']),
                    ]),
                    $this->group('education:payroll', '薪酬绩效', '/education/payroll', '/education/payroll/rules', 'material-symbols:price-check-outline-rounded', [
                        $this->page('education:payroll:rule:page', 'Salary Rules', '/education/payroll/rules', 'education/views/payroll/SalaryRuleList', ['education:payroll:rule:save']),
                        $this->page('education:payroll:batch:page', 'Salary Batches', '/education/payroll/batches', 'education/views/payroll/SalaryBatchList', ['education:payroll:batch:calculate']),
                        $this->page('education:payroll:slip:page', 'Salary Slips', '/education/payroll/slips', 'education/views/payroll/SalarySlipList'),
                        $this->page('education:payroll:review:page', 'Salary Reviews', '/education/payroll/reviews', 'education/views/payroll/SalaryReviewList', ['education:payroll:review:handle']),
                        $this->page('education:payroll:payment:page', 'Salary Payments', '/education/payroll/payments', 'education/views/payroll/SalaryPaymentList', ['education:payroll:payment:confirm']),
                        $this->page('education:payroll:dispute:page', 'Workload Disputes', '/education/payroll/disputes', 'education/views/payroll/WorkloadDisputeList', ['education:payroll:dispute:handle']),
                        $this->page('education:payroll:performance:page', 'Teacher Performance', '/education/payroll/performance', 'education/views/payroll/TeacherPerformanceDashboard'),
                    ]),
                    $this->group('education:group', '集团管控', '/education/group', '/education/group/dashboard', 'material-symbols:account-tree-outline-rounded', [
                        $this->page('education:group:metric:page', 'Group Dashboard', '/education/group/dashboard', 'education/views/group/GroupOperationDashboard'),
                        $this->page('education:group:org:tree', 'Org Units', '/education/group/org-units', 'education/views/group/OrgUnitTree', ['education:group:org:save']),
                        $this->page('education:group:data-permission:page', 'Data Permissions', '/education/group/data-permissions', 'education/views/group/DataPermissionList', ['education:group:data-permission:save']),
                        $this->page('education:group:approval-template:page', 'Approval Templates', '/education/group/approval-templates', 'education/views/group/ApprovalTemplateList', ['education:group:approval-template:save']),
                        $this->page('education:group:approval-task:page', 'Approval Tasks', '/education/group/approval-tasks', 'education/views/group/ApprovalTaskList', ['education:group:approval-task:handle']),
                        $this->page('education:group:contract:page', 'Contracts', '/education/group/contracts', 'education/views/group/ContractList', ['education:group:contract:save']),
                        $this->page('education:group:contract-renewal:page', 'Contract Renewals', '/education/group/contract-renewals', 'education/views/group/ContractRenewalList'),
                        $this->page('education:group:franchise:page', 'Franchises', '/education/group/franchises', 'education/views/group/FranchiseRecordList', ['education:group:franchise:save']),
                        $this->page('education:group:risk-audit:page', 'Risk Audits', '/education/group/risk-audits', 'education/views/group/RiskAuditEventList'),
                    ]),
                    $this->group('education:family', '家校服务', '/education/family', '/education/family/homework', 'material-symbols:family-restroom-rounded', [
                        $this->page('education:family:comment-template:page', 'Comment Templates', '/education/family/comment-templates', 'education/views/family/CommentTemplateList', ['education:family:comment-template:save']),
                        $this->page('education:family:performance-tag:page', 'Performance Tags', '/education/family/performance-tags', 'education/views/family/PerformanceTagList', ['education:family:performance-tag:save']),
                        $this->page('education:family:homework:page', 'Homework', '/education/family/homework', 'education/views/family/HomeworkAssignmentList', ['education:family:homework:save', 'education:family:homework:review']),
                        $this->page('education:family:report:page', 'Learning Reports', '/education/family/reports', 'education/views/family/LearningReportList', ['education:family:report:publish']),
                        $this->page('education:family:growth:page', 'Growth Records', '/education/family/growth-records', 'education/views/family/GrowthRecordList', ['education:family:growth:save']),
                        $this->page('education:family:message:page', 'Family Messages', '/education/family/messages', 'education/views/family/FamilyMessageMonitor', ['education:family:message:reply']),
                        $this->page('education:family:quality:page', 'Service Quality', '/education/family/quality', 'education/views/family/ServiceQualityDashboard'),
                    ]),
                    $this->group('education:ai', 'AI 助手', '/education/ai', '/education/ai/model-configs', 'material-symbols:smart-toy-outline-rounded', [
                        $this->page('education:ai:model-config:page', '模型配置', '/education/ai/model-configs', 'education/views/ai/AiModelConfigList', ['education:ai:model-config:save', 'education:ai:feature-setting:save']),
                        $this->page('education:ai:prompt:page', '提示词模板', '/education/ai/prompts', 'education/views/ai/PromptTemplateList', ['education:ai:prompt:save', 'education:ai:prompt:publish']),
                        $this->page('education:ai:generation:page', '生成任务', '/education/ai/generation-tasks', 'education/views/ai/GenerationTaskList', ['education:ai:generation:create']),
                        $this->page('education:ai:review:page', 'AI 审核', '/education/ai/reviews', 'education/views/ai/AiReviewList', ['education:ai:review:approve', 'education:ai:review:handle']),
                        $this->page('education:ai:risk-score:page', '风险评分', '/education/ai/risk-scores', 'education/views/ai/RiskScoreList'),
                        $this->page('education:ai:data-question:page', '数据问答', '/education/ai/data-questions', 'education/views/ai/DataQuestionWorkbench', ['education:ai:data-question:create']),
                        $this->page('education:ai:recommendation:page', '智能推荐', '/education/ai/recommendations', 'education/views/ai/AiRecommendationList', ['education:ai:recommendation:adopt', 'education:ai:recommendation:handle']),
                        $this->page('education:ai:usage:summary', '用量统计', '/education/ai/usage', 'education/views/ai/UsageDashboard'),
                        $this->page('education:ai:safety:page', '安全事件', '/education/ai/safety-events', 'education/views/ai/SafetyEventList', ['education:ai:safety:handle']),
                    ]),
                    $this->group('education:workflow', '工作流中心', '/education/workflow', '/education/workflow/tasks', 'material-symbols:account-tree-outline-rounded', [
                        $this->page('education:workflow:rule:page', 'Rules', '/education/workflow/rules', 'education/views/workflow/WorkflowRuleList', ['education:workflow:rule:save']),
                        $this->page('education:workflow:task:page', 'Tasks', '/education/workflow/tasks', 'education/views/workflow/WorkflowTaskWorkbench', ['education:workflow:task:handle']),
                        $this->page('education:workflow:alert:page', 'Alerts', '/education/workflow/alerts', 'education/views/workflow/OperationAlertList', ['education:workflow:alert:convert']),
                        $this->page('education:workflow:sla:page', 'SLA Policies', '/education/workflow/sla-policies', 'education/views/workflow/SlaPolicyList', ['education:workflow:sla:save']),
                        $this->page('education:workflow:escalation:page', 'Escalation', '/education/workflow/escalation-policies', 'education/views/workflow/EscalationPolicyList', ['education:workflow:escalation:save']),
                        $this->page('education:workflow:template:page', 'Templates', '/education/workflow/templates', 'education/views/workflow/WorkflowTemplateList', ['education:workflow:template:save']),
                        $this->page('education:workflow:metric:page', 'Metrics', '/education/workflow/metrics', 'education/views/workflow/WorkflowMetricDashboard'),
                    ]),
                    $this->group('education:growth', '增长转化', '/education/growth', '/education/growth/workbench', 'material-symbols:trending-up-rounded', [
                        $this->page('education:growth:workbench:view', 'Workbench', '/education/growth/workbench', 'education/views/growth/GrowthWorkbench'),
                        $this->page('education:growth:score:recalculate', 'Lead Scores', '/education/growth/lead-scores', 'education/views/growth/LeadScoreList'),
                        $this->page('education:growth:ai-script:generate', 'AI Scripts', '/education/growth/ai-talk-scripts', 'education/views/growth/AiTalkScriptWorkbench'),
                        $this->page('education:growth:strategy:save', 'Strategies', '/education/growth/followup-strategies', 'education/views/growth/FollowupStrategyList'),
                        $this->page('education:growth:trial-conversion:view', 'Trial Conversion', '/education/growth/trial-conversions', 'education/views/growth/TrialConversionList'),
                        $this->page('education:growth:channel-roi:page', 'Channel ROI', '/education/growth/channel-roi', 'education/views/growth/ChannelRoiDashboard'),
                        $this->page('education:growth:consultant-metric:page', 'Consultant Metrics', '/education/growth/consultant-metrics', 'education/views/growth/ConsultantMetricDashboard'),
                        $this->page('education:growth:loss:create', 'Loss Reasons', '/education/growth/loss-reasons', 'education/views/growth/LossReasonReport'),
                    ]),
                    $this->group('education:standards', '标准化管理', '/education/standards', '/education/standards/packages', 'material-symbols:rule-folder-outline-rounded', [
                        $this->page('education:standards:package:page', 'Packages', '/education/standards/packages', 'education/views/standards/ServicePackageList', ['education:standards:package:save', 'education:standards:package:publish']),
                        $this->page('education:standards:stage-goal:page', 'Stage Goals', '/education/standards/stage-goals', 'education/views/standards/StageGoalEditor', ['education:standards:stage-goal:save']),
                        $this->page('education:standards:ability:page', 'Ability Points', '/education/standards/ability-points', 'education/views/standards/AbilityPointList', ['education:standards:ability:save']),
                        $this->page('education:standards:trial:page', 'Trial Standards', '/education/standards/trial', 'education/views/standards/TrialStandardEditor', ['education:standards:trial:save']),
                        $this->page('education:standards:delivery:page', 'Delivery Standards', '/education/standards/delivery', 'education/views/standards/DeliveryStandardEditor', ['education:standards:delivery:save']),
                        $this->page('education:standards:template:page', 'Templates', '/education/standards/templates', 'education/views/standards/ServiceTemplateList', ['education:standards:template:save']),
                        $this->page('education:standards:material:page', 'Materials', '/education/standards/materials', 'education/views/standards/CourseMaterialList', ['education:standards:material:save']),
                        $this->page('education:standards:quality:page', 'Feedback', '/education/standards/feedback', 'education/views/standards/CourseFeedbackList'),
                        $this->page('education:standards:quality-dashboard:page', 'Quality', '/education/standards/quality', 'education/views/standards/CourseQualityDashboard'),
                        $this->page('education:standards:version:page', 'Versions', '/education/standards/versions', 'education/views/standards/StandardVersionList', ['education:standards:version:publish']),
                        $this->page('education:standards:review:page', 'Reviews', '/education/standards/reviews', 'education/views/standards/StandardReviewList', ['education:standards:review:handle']),
                    ]),
                    $this->group('education:content', '内容教研', '/education/content', '/education/content/materials', 'material-symbols:folder-open-outline-rounded', [
                        $this->page('education:content:material:page', 'Materials', '/education/content/materials', 'education/views/content/LearningMaterialList', ['education:content:material:save', 'education:content:material:publish', 'education:content:material:withdraw']),
                        $this->page('education:content:version:page', 'Versions', '/education/content/material-versions', 'education/views/content/MaterialVersionList', ['education:content:version:create']),
                        $this->page('education:content:attachment:page', 'Attachments', '/education/content/attachments', 'education/views/content/MaterialAttachmentList', ['education:content:attachment:upload']),
                        $this->page('education:content:relation:page', 'Relations', '/education/content/relations', 'education/views/content/MaterialRelationEditor', ['education:content:relation:sync']),
                        $this->page('education:content:student-work:page', 'Student Works', '/education/content/student-works', 'education/views/content/StudentWorkList', ['education:content:student-work:publish', 'education:content:student-work:withdraw']),
                        $this->page('education:content:showcase:page', 'Showcases', '/education/content/showcases', 'education/views/content/ShowcaseList', ['education:content:showcase:save', 'education:content:showcase:publish', 'education:content:showcase:withdraw']),
                        $this->page('education:content:review:page', 'Reviews', '/education/content/reviews', 'education/views/content/ContentReviewList', ['education:content:review:handle']),
                        $this->page('education:content:metric:page', 'Usage Metrics', '/education/content/metrics', 'education/views/content/MaterialUsageDashboard'),
                    ]),
                ],
            ]),
        ];
    }

    private function group(string $name, string $title, string $path, string $redirect, string $icon, array $children): array
    {
        return $this->menu($name, $title, $path, '', $icon, [
            'redirect' => $redirect,
            'children' => $children,
        ]);
    }

    private function page(string $name, string $title, string $path, string $component, array $buttons = []): array
    {
        return $this->menu($name, $title, $path, $component, 'material-symbols:article-outline-rounded', [
            'children' => array_map(fn (string $button): array => $this->button($button), $buttons),
        ]);
    }

    private function button(string $name): array
    {
        return [
            'name' => $name,
            'meta' => new Meta([
                'title' => $this->titleFromPermission($name),
                'type' => 'B',
                'auth' => [$name],
            ]),
        ];
    }

    private function menu(string $name, string $title, string $path, string $component, string $icon, array $extra = []): array
    {
        return array_merge([
            'name' => $name,
            'path' => $path,
            'component' => $component,
            'meta' => new Meta([
                'title' => self::MENU_TITLES[$name] ?? $title,
                'icon' => $icon,
                'type' => 'M',
                'hidden' => false,
                'componentPath' => 'modules/',
                'componentSuffix' => '.vue',
                'breadcrumbEnable' => true,
                'copyright' => true,
                'cache' => true,
                'affix' => false,
                'auth' => [$name],
            ]),
        ], $extra);
    }

    private function titleFromPermission(string $name): string
    {
        $action = trim((string) mb_strrchr($name, ':'), ': ');

        return self::ACTION_TITLES[$action] ?? ucwords(str_replace('-', ' ', $action));
    }
}
