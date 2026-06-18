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
        $v12 = Db::table('menu')->where('name', 'education:content')->first();
        $tenantPage = Db::table('menu')->where('name', 'education:foundation:tenant:page')->first();
        $coursePage = Db::table('menu')->where('name', 'education:academic:course:page')->first();
        $tenantSaveButton = Db::table('menu')->where('name', 'education:foundation:tenant:save')->first();
        $contentReviewButton = Db::table('menu')->where('name', 'education:content:review:handle')->first();

        self::assertNotNull($root);
        self::assertNotNull($academicGroup);
        self::assertNotNull($v12);
        self::assertNotNull($tenantPage);
        self::assertNotNull($coursePage);
        self::assertNotNull($tenantSaveButton);
        self::assertNotNull($contentReviewButton);
        self::assertSame('/education', $root->path);
        self::assertSame('/education/foundation/tenants', $tenantPage->path);
        self::assertSame('/education/content/materials', $v12->redirect);

        self::assertSame('教育 SaaS', json_decode((string) $root->meta, true)['title']);
        self::assertSame('V1 教务管理', json_decode((string) $academicGroup->meta, true)['title']);
        self::assertSame('机构管理', json_decode((string) $tenantPage->meta, true)['title']);
        self::assertSame('课程管理', json_decode((string) $coursePage->meta, true)['title']);
        self::assertSame('保存', json_decode((string) $tenantSaveButton->meta, true)['title']);
        self::assertSame('处理', json_decode((string) $contentReviewButton->meta, true)['title']);
        self::assertSame('B', json_decode((string) $contentReviewButton->meta, true)['type']);

        self::assertSame(1, Db::table('menu')->where('name', 'education')->count());
        self::assertSame(1, Db::table('menu')->where('name', 'education:content:review:handle')->count());
        self::assertGreaterThan(80, Db::table('menu')->where('name', 'like', 'education%')->count());

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
}
