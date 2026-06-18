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

use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class TenantCampusMigrationTest extends TestCase
{
    public function testTenantAndCampusTablesExistWithRequiredColumns(): void
    {
        self::assertTrue(Schema::hasTable('edu_tenants'));
        self::assertTrue(Schema::hasTable('edu_campuses'));

        foreach (['id', 'name', 'code', 'status', 'settings', 'enabled_at', 'disabled_at', 'deleted_at'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_tenants', $column), "edu_tenants missing {$column}");
        }

        foreach (['id', 'tenant_id', 'name', 'code', 'status', 'settings', 'deleted_at'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_campuses', $column), "edu_campuses missing {$column}");
        }
    }
}
