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
        $contentGroup = Db::table('menu')->where('name', 'education:content')->first();
        $tenantPage = Db::table('menu')->where('name', 'education:foundation:tenant:page')->first();
        $coursePage = Db::table('menu')->where('name', 'education:academic:course:page')->first();
        $tenantSaveButton = Db::table('menu')->where('name', 'education:foundation:tenant:save')->first();
        $contentReviewButton = Db::table('menu')->where('name', 'education:content:review:handle')->first();

        self::assertNotNull($root);
        self::assertNotNull($academicGroup);
        self::assertNotNull($contentGroup);
        self::assertNotNull($tenantPage);
        self::assertNotNull($coursePage);
        self::assertNotNull($tenantSaveButton);
        self::assertNotNull($contentReviewButton);
        self::assertSame('/education', $root->path);
        self::assertSame('/education/foundation/tenants', $tenantPage->path);
        self::assertSame('/education/content/materials', $contentGroup->redirect);

        self::assertSame('教育管理', $this->menuTitle($root));
        self::assertSame('教务管理', $this->menuTitle($academicGroup));
        self::assertSame('内容教研', $this->menuTitle($contentGroup));
        self::assertSame('机构管理', $this->menuTitle($tenantPage));
        self::assertSame('课程管理', $this->menuTitle($coursePage));
        self::assertSame('保存', $this->menuTitle($tenantSaveButton));
        self::assertSame('处理', $this->menuTitle($contentReviewButton));
        self::assertSame('B', json_decode((string) $contentReviewButton->meta, true)['type']);

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
        self::assertSame((int) $root->id, (int) $contentGroup->parent_id);

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
}
