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
final class AttendanceConsumptionMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->attendanceConsumptionMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testAttendanceConsumptionTablesExist(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasTable($table), "{$table} table missing");
        }
    }

    public function testAttendanceColumnsAndIndexesExist(): void
    {
        foreach ($this->requiredColumns()['edu_lesson_attendances'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_lesson_attendances', $column), "edu_lesson_attendances missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_lesson_attendances', 'uk_edu_lesson_attendances_tenant_lesson_student'));
        self::assertTrue($this->hasIndex('edu_lesson_attendances', 'idx_edu_lesson_attendances_batch_no'));
    }

    public function testConsumptionColumnsAndIndexesExist(): void
    {
        foreach ($this->requiredColumns()['edu_lesson_consumptions'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_lesson_consumptions', $column), "edu_lesson_consumptions missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_lesson_consumptions', 'uk_edu_lesson_consumptions_tenant_no'));
        self::assertTrue($this->hasIndex('edu_lesson_consumptions', 'uk_edu_lesson_consumptions_tenant_attendance_source'));
        self::assertTrue($this->hasIndex('edu_lesson_consumptions', 'uk_edu_lesson_consumptions_tenant_original_source'));
    }

    public function testAdjustmentColumnsAndIndexesExist(): void
    {
        foreach ($this->requiredColumns()['edu_account_adjustments'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_account_adjustments', $column), "edu_account_adjustments missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_account_adjustments', 'uk_edu_account_adjustments_tenant_no'));
        self::assertTrue($this->hasIndex('edu_account_adjustments', 'uk_edu_account_adjustments_tenant_original_type'));
    }

    public function testRollbackDropsTablesInDependencyOrder(): void
    {
        $migration = $this->attendanceConsumptionMigration();

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
            'edu_lesson_attendances' => [
                'id',
                'tenant_id',
                'campus_id',
                'lesson_id',
                'lesson_student_id',
                'class_id',
                'course_id',
                'student_id',
                'account_id',
                'attendance_status',
                'consume_policy',
                'planned_units',
                'consumed_units',
                'consumption_status',
                'submitted_at',
                'submitted_by',
                'attendance_batch_no',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lesson_consumptions' => [
                'id',
                'tenant_id',
                'campus_id',
                'consumption_no',
                'account_id',
                'student_id',
                'course_id',
                'lesson_id',
                'lesson_student_id',
                'attendance_id',
                'source_type',
                'direction',
                'units',
                'before_available_units',
                'after_available_units',
                'before_consumed_units',
                'after_consumed_units',
                'status',
                'original_consumption_id',
                'reversed_at',
                'reversed_by',
                'reason',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_account_adjustments' => [
                'id',
                'tenant_id',
                'campus_id',
                'adjustment_no',
                'account_id',
                'student_id',
                'course_id',
                'adjustment_type',
                'direction',
                'units',
                'before_available_units',
                'after_available_units',
                'before_adjusted_units',
                'after_adjusted_units',
                'status',
                'original_adjustment_id',
                'rolled_back_at',
                'rolled_back_by',
                'reason',
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

    private function attendanceConsumptionMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010400_create_v1_attendance_consumption_tables.php';
    }
}
