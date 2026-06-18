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

namespace HyperfTests\Feature\Education\Academic;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class GuardianNoticeMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->noticeMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testNoticeTablesExist(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasTable($table), "{$table} table missing");
        }
    }

    public function testNoticeColumnsAndIndexesExist(): void
    {
        foreach ($this->requiredColumns()['edu_notices'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_notices', $column), "edu_notices missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_notices', 'uk_edu_notices_tenant_no'));
        self::assertTrue($this->hasIndex('edu_notices', 'idx_edu_notices_tenant_status_publish'));
        self::assertTrue($this->hasIndex('edu_notices', 'idx_edu_notices_tenant_target'));
        self::assertTrue($this->hasIndex('edu_notices', 'idx_edu_notices_tenant_campus_status'));
        self::assertTrue($this->hasIndex('edu_notices', 'idx_edu_notices_tenant_type_priority'));
    }

    public function testReceiptColumnsAndUniqueKeyExist(): void
    {
        foreach ($this->requiredColumns()['edu_notice_receipts'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_notice_receipts', $column), "edu_notice_receipts missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_notice_receipts', 'uk_edu_notice_receipts_tenant_notice_guardian_student'));
        self::assertTrue($this->hasIndex('edu_notice_receipts', 'idx_edu_notice_receipts_tenant_guardian_status'));
        self::assertTrue($this->hasIndex('edu_notice_receipts', 'idx_edu_notice_receipts_tenant_student_status'));
        self::assertTrue($this->hasIndex('edu_notice_receipts', 'idx_edu_notice_receipts_tenant_notice_status'));
    }

    public function testRollbackDropsReceiptsBeforeNotices(): void
    {
        $migration = $this->noticeMigration();

        $migration->down();

        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertFalse(Schema::hasTable($table), "{$table} table should be rolled back");
        }

        $migration->up();

        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasTable($table), "{$table} table should be re-created");
        }
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredColumns(): array
    {
        return [
            'edu_notices' => [
                'id',
                'tenant_id',
                'campus_id',
                'notice_no',
                'notice_type',
                'target_type',
                'target_id',
                'title',
                'content',
                'priority',
                'status',
                'published_at',
                'published_by',
                'withdrawn_at',
                'withdrawn_by',
                'withdraw_reason',
                'expire_at',
                'receipt_count',
                'read_count',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_notice_receipts' => [
                'id',
                'tenant_id',
                'campus_id',
                'notice_id',
                'guardian_id',
                'student_id',
                'relation',
                'guardian_name_snapshot',
                'student_name_snapshot',
                'status',
                'delivered_at',
                'read_at',
                'read_by_profile_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ];
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return $indexes !== [];
    }

    private function noticeMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010700_create_v1_notice_tables.php';
    }
}
