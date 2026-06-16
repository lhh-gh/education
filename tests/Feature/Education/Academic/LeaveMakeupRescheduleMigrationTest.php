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
final class LeaveMakeupRescheduleMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->leaveChangeMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testLeaveChangeTablesExist(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasTable($table), "{$table} table missing");
        }
    }

    public function testLeaveRequestColumnsAndIndexesExist(): void
    {
        foreach ($this->requiredColumns()['edu_leave_requests'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_leave_requests', $column), "edu_leave_requests missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_leave_requests', 'uk_edu_leave_requests_tenant_no'));
        self::assertTrue($this->hasIndex('edu_leave_requests', 'uk_edu_leave_requests_tenant_lesson_student'));
        self::assertTrue($this->hasIndex('edu_leave_requests', 'idx_edu_leave_requests_tenant_campus_status'));
        self::assertTrue($this->hasIndex('edu_leave_requests', 'idx_edu_leave_requests_tenant_makeup_lesson'));
    }

    public function testLessonChangeColumnsAndIndexesExist(): void
    {
        foreach ($this->requiredColumns()['edu_lesson_change_records'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_lesson_change_records', $column), "edu_lesson_change_records missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_lesson_change_records', 'uk_edu_lesson_change_records_tenant_no'));
        self::assertTrue($this->hasIndex('edu_lesson_change_records', 'idx_edu_lesson_change_records_tenant_source_lesson'));
        self::assertTrue($this->hasIndex('edu_lesson_change_records', 'idx_edu_lesson_change_records_tenant_target_lesson'));
        self::assertTrue($this->hasIndex('edu_lesson_change_records', 'idx_edu_lesson_change_records_tenant_leave'));
    }

    public function testRollbackDropsTablesInDependencyOrder(): void
    {
        $migration = $this->leaveChangeMigration();

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
            'edu_leave_requests' => [
                'id',
                'tenant_id',
                'campus_id',
                'leave_no',
                'source',
                'leave_type',
                'lesson_id',
                'lesson_student_id',
                'class_id',
                'course_id',
                'student_id',
                'account_id',
                'guardian_id',
                'teacher_id',
                'reason',
                'status',
                'requested_at',
                'reviewed_at',
                'reviewed_by',
                'review_remark',
                'cancelled_at',
                'cancelled_by',
                'cancel_reason',
                'makeup_required',
                'makeup_lesson_id',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lesson_change_records' => [
                'id',
                'tenant_id',
                'campus_id',
                'change_no',
                'change_type',
                'status',
                'leave_request_id',
                'source_lesson_id',
                'source_lesson_student_id',
                'target_lesson_id',
                'class_id',
                'course_id',
                'student_id',
                'account_id',
                'source_teacher_id',
                'target_teacher_id',
                'source_classroom_id',
                'target_classroom_id',
                'source_start_at',
                'source_end_at',
                'target_start_at',
                'target_end_at',
                'lesson_units',
                'reason',
                'cancelled_at',
                'cancelled_by',
                'cancel_reason',
                'created_by',
                'updated_by',
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

    private function leaveChangeMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010500_create_v1_leave_change_tables.php';
    }
}
