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
final class UserProfileMigrationTest extends TestCase
{
    public function testProfileAndScopeTablesHaveRequiredColumnsAndIndexes(): void
    {
        self::assertTrue(Schema::hasTable('edu_user_profiles'));
        self::assertTrue(Schema::hasTable('edu_user_campus_scopes'));

        foreach ([
            'profile_key',
            'tenant_id',
            'user_id',
            'role_code',
            'display_name',
            'status',
            'current_campus_id',
            'deleted_at',
        ] as $column) {
            self::assertTrue(Schema::hasColumn('edu_user_profiles', $column), "edu_user_profiles missing {$column}");
        }

        foreach (['tenant_id', 'user_profile_id', 'user_id', 'campus_id'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_user_campus_scopes', $column), "edu_user_campus_scopes missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_user_profiles', 'uk_edu_user_profiles_profile_key'));
        self::assertTrue($this->hasIndex('edu_user_campus_scopes', 'uk_edu_user_campus_scopes_tenant_user_campus'));
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return $indexes !== [];
    }
}
