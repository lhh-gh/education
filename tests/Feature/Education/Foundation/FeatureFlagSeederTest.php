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
final class FeatureFlagSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Db::table('edu_feature_flags')->delete();
    }

    public function testFeatureFlagSeederCreatesV1ToV12Flags(): void
    {
        self::assertFileExists(BASE_PATH . '/databases/seeders/EducationFoundationFeatureFlagSeeder.php');
        require_once BASE_PATH . '/databases/seeders/EducationFoundationFeatureFlagSeeder.php';

        $seeder = new \EducationFoundationFeatureFlagSeeder();
        $seeder->run();
        $seeder->run();

        $v1 = Db::table('edu_feature_flags')
            ->where('owner_key', 'system')
            ->where('feature_code', 'education.v1.core_academic')
            ->first();
        $v12 = Db::table('edu_feature_flags')
            ->where('owner_key', 'system')
            ->where('feature_code', 'education.v12.learning_content')
            ->first();

        self::assertNotNull($v1);
        self::assertNotNull($v12);
        self::assertSame(1, (int) $v1->enabled);
        self::assertSame(0, (int) $v12->enabled);
        self::assertSame(12, Db::table('edu_feature_flags')->where('owner_key', 'system')->count());
        self::assertSame(1, Db::table('edu_feature_flags')->where('owner_key', 'system')->where('feature_code', 'education.v1.core_academic')->count());
    }
}
