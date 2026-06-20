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

namespace HyperfTests\Feature\Education\Foundation;

use Hyperf\DbConnection\Db;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class EducationMenuSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $ids = Db::table('menu')
            ->where('name', 'like', 'education%')
            ->pluck('id')
            ->all();

        if ($ids !== []) {
            Db::table('role_belongs_menu')->whereIn('menu_id', $ids)->delete();
            Db::table('menu')->whereIn('id', $ids)->delete();
        }

        Db::table('role')->whereIn('code', [
            'superAdmin',
            'education_tenant_admin',
            'education_teacher',
            'education_guardian',
        ])->delete();
    }

    public function testEducationMenuSeederCreatesMenusAndRoleBindingsIdempotently(): void
    {
        self::assertFileExists(BASE_PATH . '/databases/seeders/EducationMenuSeeder.php');
        require_once BASE_PATH . '/databases/seeders/EducationMenuSeeder.php';

        Db::table('role')->insert([
            [
                'name' => 'Super Admin',
                'code' => 'superAdmin',
                'status' => 1,
                'sort' => 0,
                'remark' => 'test',
                'created_by' => 0,
                'updated_by' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Education Tenant Admin',
                'code' => 'education_tenant_admin',
                'status' => 1,
                'sort' => 0,
                'remark' => 'test',
                'created_by' => 0,
                'updated_by' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Education Teacher',
                'code' => 'education_teacher',
                'status' => 1,
                'sort' => 0,
                'remark' => 'test',
                'created_by' => 0,
                'updated_by' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Education Guardian',
                'code' => 'education_guardian',
                'status' => 1,
                'sort' => 0,
                'remark' => 'test',
                'created_by' => 0,
                'updated_by' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);

        $seeder = new \EducationMenuSeeder();
        $seeder->run();
        $seeder->run();

        $root = Db::table('menu')->where('name', 'education')->first();
        $academicGroup = Db::table('menu')->where('name', 'education:academic')->first();
        $financeGroup = Db::table('menu')->where('name', 'education:finance')->first();
        $payrollGroup = Db::table('menu')->where('name', 'education:payroll')->first();
        $contentGroup = Db::table('menu')->where('name', 'education:content')->first();
        $tenantPage = Db::table('menu')->where('name', 'education:foundation:tenant:page')->first();
        $coursePage = Db::table('menu')->where('name', 'education:academic:course:page')->first();
        $tenantSaveButton = Db::table('menu')->where('name', 'education:foundation:tenant:save')->first();
        $contentReviewButton = Db::table('menu')->where('name', 'education:content:review:handle')->first();
        $aiModelSaveButton = Db::table('menu')->where('name', 'education:ai:model-config:save')->first();
        $aiFeatureSaveButton = Db::table('menu')->where('name', 'education:ai:feature-setting:save')->first();
        $aiDataQuestionPage = Db::table('menu')->where('name', 'education:ai:data-question:page')->first();
        $aiDataQuestionCreateButton = Db::table('menu')->where('name', 'education:ai:data-question:create')->first();
        $admissionLeadCreateButton = Db::table('menu')->where('name', 'education:admissions:lead:create')->first();
        $admissionLeadSourceCreateButton = Db::table('menu')->where('name', 'education:admissions:lead-source:create')->first();
        $admissionTrialCreateButton = Db::table('menu')->where('name', 'education:admissions:trial:create')->first();
        $admissionTrialAttendanceButton = Db::table('menu')->where('name', 'education:admissions:trial:attendance')->first();
        $growthGroup = Db::table('menu')->where('name', 'education:growth')->first();
        $standardsGroup = Db::table('menu')->where('name', 'education:standards')->first();

        self::assertNotNull($root);
        self::assertNotNull($academicGroup);
        self::assertNotNull($financeGroup);
        self::assertNotNull($payrollGroup);
        self::assertNotNull($contentGroup);
        self::assertNotNull($tenantPage);
        self::assertNotNull($coursePage);
        self::assertNotNull($tenantSaveButton);
        self::assertNotNull($contentReviewButton);
        self::assertNotNull($aiModelSaveButton);
        self::assertNotNull($aiFeatureSaveButton);
        self::assertNotNull($aiDataQuestionPage);
        self::assertNotNull($aiDataQuestionCreateButton);
        self::assertNotNull($admissionLeadCreateButton);
        self::assertNotNull($admissionLeadSourceCreateButton);
        self::assertNotNull($admissionTrialCreateButton);
        self::assertNotNull($admissionTrialAttendanceButton);
        self::assertNotNull($growthGroup);
        self::assertNotNull($standardsGroup);
        self::assertSame('/education', $root->path);
        self::assertSame('/education/foundation/tenants', $tenantPage->path);
        self::assertSame('/education/content/materials', $contentGroup->redirect);

        self::assertSame('教育管理', $this->menuTitle($root));
        self::assertSame('教务管理', $this->menuTitle($academicGroup));
        self::assertSame('财务中心', $this->menuTitle($financeGroup));
        self::assertSame('内容教研', $this->menuTitle($contentGroup));
        self::assertSame('机构管理', $this->menuTitle($tenantPage));
        self::assertSame('课程管理', $this->menuTitle($coursePage));
        self::assertSame('保存', $this->menuTitle($tenantSaveButton));
        self::assertSame('处理', $this->menuTitle($contentReviewButton));
        self::assertSame('B', json_decode((string) $contentReviewButton->meta, true)['type']);
        self::assertSame('保存', $this->menuTitle($aiModelSaveButton));
        self::assertSame('保存', $this->menuTitle($aiFeatureSaveButton));
        self::assertSame('B', json_decode((string) $aiFeatureSaveButton->meta, true)['type']);
        self::assertSame('数据问答', $this->menuTitle($aiDataQuestionPage));
        self::assertSame('新增', $this->menuTitle($aiDataQuestionCreateButton));
        self::assertSame('B', json_decode((string) $aiDataQuestionCreateButton->meta, true)['type']);
        self::assertSame('新增', $this->menuTitle($admissionLeadCreateButton));
        self::assertSame('新增', $this->menuTitle($admissionLeadSourceCreateButton));
        self::assertSame('新增', $this->menuTitle($admissionTrialCreateButton));
        self::assertSame('考勤', $this->menuTitle($admissionTrialAttendanceButton));
        self::assertSame('B', json_decode((string) $admissionTrialAttendanceButton->meta, true)['type']);

        $moduleTitles = Db::table('menu')
            ->where('parent_id', $root->id)
            ->orderBy('sort')
            ->get()
            ->map(fn (object $menu): string => $this->menuTitle($menu))
            ->all();

        self::assertSame([
            '基础设置',
            '教务管理',
            '运营中心',
            '招生获客',
            '财务中心',
            '薪酬绩效',
            '集团管控',
            '家校服务',
            'AI 助手',
            '工作流中心',
            '增长转化',
            '标准化管理',
            '内容教研',
        ], $moduleTitles);

        self::assertSame((int) $root->id, (int) $academicGroup->parent_id);
        self::assertSame((int) $root->id, (int) $financeGroup->parent_id);
        self::assertSame((int) $root->id, (int) $payrollGroup->parent_id);
        self::assertSame('material-symbols:price-check-rounded', $this->menuIcon($payrollGroup));
        self::assertSame((int) $root->id, (int) $contentGroup->parent_id);

        $financePageTitles = Db::table('menu')
            ->where('parent_id', $financeGroup->id)
            ->orderBy('sort')
            ->get()
            ->map(fn (object $menu): string => $this->menuTitle($menu))
            ->all();

        self::assertSame([
            '财务看板',
            '订单管理',
            '收款记录',
            '支付渠道',
            '退费管理',
            '票据管理',
            '对账管理',
        ], $financePageTitles);

        self::assertSame(1, Db::table('menu')->where('name', 'education')->count());
        self::assertSame(1, Db::table('menu')->where('name', 'education:content:review:handle')->count());
        self::assertGreaterThan(80, Db::table('menu')->where('name', 'like', 'education%')->count());
        Db::table('menu')
            ->where('name', 'like', 'education%')
            ->get()
            ->each(function (object $menu): void {
                self::assertStringNotContainsString('教育 SaaS', $this->menuTitle($menu));
                self::assertDoesNotMatchRegularExpression('/V\d+/u', $this->menuTitle($menu));
            });

        $adminRoleId = Db::table('role')->where('code', 'education_tenant_admin')->value('id');
        $guardianRoleId = Db::table('role')->where('code', 'education_guardian')->value('id');
        $educationMenuIds = Db::table('menu')->where('name', 'like', 'education%')->pluck('id')->all();
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
            'education:ai:recommendation:handle',
            'education:ai:safety:handle',
        ];

        foreach ($expectedAiButtons as $buttonName) {
            $button = Db::table('menu')->where('name', $buttonName)->first();

            self::assertNotNull($button, \sprintf('Missing AI button permission [%s].', $buttonName));
            self::assertSame('B', json_decode((string) $button->meta, true)['type']);
            self::assertSame(
                1,
                Db::table('role_belongs_menu')->where('role_id', $adminRoleId)->where('menu_id', $button->id)->count(),
                \sprintf('AI button permission [%s] is not bound to education tenant admin.', $buttonName)
            );
        }

        $expectedAdmissionButtons = [
            'education:admissions:lead-source:create',
            'education:admissions:lead:create',
            'education:admissions:lead:assign',
            'education:admissions:lead:follow',
            'education:admissions:lead:convert',
            'education:admissions:trial:create',
            'education:admissions:trial:attendance',
            'education:admissions:trial-feedback:create',
        ];

        foreach ($expectedAdmissionButtons as $buttonName) {
            $button = Db::table('menu')->where('name', $buttonName)->first();

            self::assertNotNull($button, \sprintf('Missing admission button permission [%s].', $buttonName));
            self::assertSame('B', json_decode((string) $button->meta, true)['type']);
            self::assertSame(
                1,
                Db::table('role_belongs_menu')->where('role_id', $adminRoleId)->where('menu_id', $button->id)->count(),
                \sprintf('Admission button permission [%s] is not bound to education tenant admin.', $buttonName)
            );
        }

        $expectedGrowthPermissions = [
            'education:growth:score:recalculate',
            'education:growth:ai-script:generate',
            'education:growth:strategy:save',
            'education:growth:trial-conversion:view',
            'education:growth:channel-roi:page',
            'education:growth:consultant-metric:page',
            'education:growth:loss:create',
        ];

        foreach ($expectedGrowthPermissions as $permissionName) {
            $permission = Db::table('menu')->where('name', $permissionName)->first();

            self::assertNotNull($permission, \sprintf('Missing growth permission [%s].', $permissionName));
            self::assertSame(
                1,
                Db::table('role_belongs_menu')->where('role_id', $adminRoleId)->where('menu_id', $permission->id)->count(),
                \sprintf('Growth permission [%s] is not bound to education tenant admin.', $permissionName)
            );
        }

        $expectedGrowthButtons = [
            'education:growth:ai-script:confirm',
            'education:growth:channel-cost:save',
            'education:growth:loss-reason:save',
            'education:growth:campaign:save',
        ];

        foreach ($expectedGrowthButtons as $buttonName) {
            $button = Db::table('menu')->where('name', $buttonName)->first();

            self::assertNotNull($button, \sprintf('Missing growth button permission [%s].', $buttonName));
            self::assertSame('B', json_decode((string) $button->meta, true)['type']);
            self::assertSame(
                1,
                Db::table('role_belongs_menu')->where('role_id', $adminRoleId)->where('menu_id', $button->id)->count(),
                \sprintf('Growth button permission [%s] is not bound to education tenant admin.', $buttonName)
            );
        }

        $growthPageTitles = Db::table('menu')
            ->where('parent_id', $growthGroup->id)
            ->orderBy('sort')
            ->get()
            ->map(fn (object $menu): string => $this->menuTitle($menu))
            ->all();

        foreach ($growthPageTitles as $title) {
            self::assertDoesNotMatchRegularExpression('/Workbench|Lead Scores|AI Scripts|Strategies|Trial Conversion|Channel ROI|Consultant Metrics|Loss Reasons/u', $title);
        }

        $expectedStandardsButtons = [
            'education:standards:package:save',
            'education:standards:package:publish',
            'education:standards:stage-goal:save',
            'education:standards:ability:save',
            'education:standards:trial:save',
            'education:standards:delivery:save',
            'education:standards:template:save',
            'education:standards:material:save',
            'education:standards:feedback:save',
            'education:standards:version:publish',
            'education:standards:version:localization',
            'education:standards:review:handle',
        ];

        foreach ($expectedStandardsButtons as $buttonName) {
            $button = Db::table('menu')->where('name', $buttonName)->first();

            self::assertNotNull($button, \sprintf('Missing standards button permission [%s].', $buttonName));
            self::assertSame('B', json_decode((string) $button->meta, true)['type']);
            self::assertSame(
                1,
                Db::table('role_belongs_menu')->where('role_id', $adminRoleId)->where('menu_id', $button->id)->count(),
                \sprintf('Standards button permission [%s] is not bound to education tenant admin.', $buttonName)
            );
        }

        $standardsPageTitles = Db::table('menu')
            ->where('parent_id', $standardsGroup->id)
            ->orderBy('sort')
            ->get()
            ->map(fn (object $menu): string => $this->menuTitle($menu))
            ->all();

        self::assertSame([
            '服务包',
            '阶段目标',
            '能力点',
            '试听标准',
            '交付标准',
            '服务模板',
            '课程资料',
            '课程反馈',
            '质量看板',
            '标准版本',
            '标准评审',
        ], $standardsPageTitles);

        foreach ($standardsPageTitles as $title) {
            self::assertDoesNotMatchRegularExpression('/Packages|Stage Goals|Ability Points|Trial Standards|Delivery Standards|Templates|Materials|Feedback|Quality|Versions|Reviews/u', $title);
        }

        $expectedContentButtons = [
            'education:content:material:save',
            'education:content:material:publish',
            'education:content:material:withdraw',
            'education:content:version:create',
            'education:content:attachment:upload',
            'education:content:relation:save',
            'education:content:student-work:publish',
            'education:content:student-work:withdraw',
            'education:content:showcase:save',
            'education:content:showcase:publish',
            'education:content:showcase:withdraw',
            'education:content:review:handle',
        ];

        foreach ($expectedContentButtons as $buttonName) {
            $button = Db::table('menu')->where('name', $buttonName)->first();

            self::assertNotNull($button, \sprintf('Missing content button permission [%s].', $buttonName));
            self::assertSame('B', json_decode((string) $button->meta, true)['type']);
            self::assertSame(
                1,
                Db::table('role_belongs_menu')->where('role_id', $adminRoleId)->where('menu_id', $button->id)->count(),
                \sprintf('Content button permission [%s] is not bound to education tenant admin.', $buttonName)
            );
        }

        $contentPageTitles = Db::table('menu')
            ->where('parent_id', $contentGroup->id)
            ->orderBy('sort')
            ->get()
            ->map(fn (object $menu): string => $this->menuTitle($menu))
            ->all();

        self::assertSame([
            '学习资料',
            '资料版本',
            '资料附件',
            '资料关联',
            '学生作品',
            '成果展陈',
            '内容审核',
            '使用看板',
        ], $contentPageTitles);

        foreach ($contentPageTitles as $title) {
            self::assertDoesNotMatchRegularExpression('/Materials|Versions|Attachments|Relations|Student Works|Showcases|Reviews|Usage Metrics/u', $title);
        }

        $expectedFinanceButtons = [
            'education:finance:order:create' => '新增',
            'education:finance:payment:offline' => '线下收款',
            'education:finance:payment-channel:save' => '保存',
            'education:finance:refund:create' => '新增',
            'education:finance:refund:approve' => '通过',
            'education:finance:receipt:issue' => '开票',
            'education:finance:reconciliation:import' => '导入',
        ];

        foreach ($expectedFinanceButtons as $buttonName => $buttonTitle) {
            $button = Db::table('menu')->where('name', $buttonName)->first();

            self::assertNotNull($button, \sprintf('Missing finance button permission [%s].', $buttonName));
            self::assertSame($buttonTitle, $this->menuTitle($button));
            self::assertSame('B', json_decode((string) $button->meta, true)['type']);
            self::assertSame(
                1,
                Db::table('role_belongs_menu')->where('role_id', $adminRoleId)->where('menu_id', $button->id)->count(),
                \sprintf('Finance button permission [%s] is not bound to education tenant admin.', $buttonName)
            );
        }

        self::assertSame(
            \count($educationMenuIds),
            Db::table('role_belongs_menu')->where('role_id', $adminRoleId)->whereIn('menu_id', $educationMenuIds)->count()
        );
        self::assertSame(
            0,
            Db::table('role_belongs_menu')->where('role_id', $guardianRoleId)->whereIn('menu_id', $educationMenuIds)->count()
        );
    }

    private function menuTitle(object $menu): string
    {
        return (string) json_decode((string) $menu->meta, true)['title'];
    }

    private function menuIcon(object $menu): string
    {
        return (string) json_decode((string) $menu->meta, true)['icon'];
    }
}
