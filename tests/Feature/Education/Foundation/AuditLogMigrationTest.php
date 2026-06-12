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
final class AuditLogMigrationTest extends TestCase
{
    public function testAuditLogTableHasRequiredColumnsAndIndexes(): void
    {
        self::assertTrue(Schema::hasTable('edu_audit_logs'));

        foreach ($this->requiredColumns() as $column) {
            self::assertTrue(Schema::hasColumn('edu_audit_logs', $column), "edu_audit_logs missing {$column}");
        }

        foreach ($this->requiredIndexes() as $index) {
            self::assertTrue($this->hasIndex('edu_audit_logs', $index), "edu_audit_logs missing index {$index}");
        }
    }

    /**
     * @return array<int, string>
     */
    private function requiredColumns(): array
    {
        return [
            'id',
            'tenant_id',
            'campus_id',
            'actor_user_id',
            'actor_type',
            'actor_role_code',
            'module',
            'resource',
            'action',
            'business_type',
            'business_id',
            'request_id',
            'ip_address',
            'user_agent',
            'method',
            'path',
            'summary',
            'before_snapshot',
            'after_snapshot',
            'diff',
            'metadata',
            'created_at',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function requiredIndexes(): array
    {
        return [
            'idx_edu_audit_logs_tenant_created',
            'idx_edu_audit_logs_tenant_module_created',
            'idx_edu_audit_logs_tenant_action_created',
            'idx_edu_audit_logs_tenant_actor_created',
            'idx_edu_audit_logs_campus_created',
            'idx_edu_audit_logs_business',
            'idx_edu_audit_logs_request',
            'idx_edu_audit_logs_created_at',
        ];
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return $indexes !== [];
    }
}
