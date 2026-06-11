<?php

declare(strict_types=1);

namespace HyperfTests\Feature\Education\Foundation;

use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

final class TenantCampusMigrationTest extends TestCase
{
    public function test_tenant_and_campus_tables_exist_with_required_columns(): void
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
