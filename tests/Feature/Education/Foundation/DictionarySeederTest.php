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
final class DictionarySeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Db::table('edu_dict_items')->delete();
        Db::table('edu_dict_types')->delete();
    }

    public function testDictionarySeederIsIdempotent(): void
    {
        self::assertFileExists(BASE_PATH . '/databases/seeders/EducationFoundationDictionarySeeder.php');
        require_once BASE_PATH . '/databases/seeders/EducationFoundationDictionarySeeder.php';

        $seeder = new \EducationFoundationDictionarySeeder();
        $seeder->run();
        $seeder->run();

        $commonStatus = Db::table('edu_dict_types')
            ->where('owner_key', 'system')
            ->where('code', 'common_status')
            ->first();

        self::assertNotNull($commonStatus);
        self::assertSame(1, Db::table('edu_dict_types')->where('owner_key', 'system')->where('code', 'common_status')->count());
        self::assertSame(1, (int) $commonStatus->is_locked);

        $values = Db::table('edu_dict_items')
            ->where('dict_type_id', $commonStatus->id)
            ->orderBy('sort_order')
            ->pluck('value')
            ->all();

        self::assertContains('enabled', $values);
        self::assertContains('disabled', $values);
    }
}
