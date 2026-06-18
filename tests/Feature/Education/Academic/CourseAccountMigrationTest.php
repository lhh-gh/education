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
final class CourseAccountMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->courseAccountMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testCourseAccountTablesHaveRequiredColumnsAndIndexes(): void
    {
        foreach ($this->requiredColumns() as $table => $columns) {
            self::assertTrue(Schema::hasTable($table), "{$table} table missing");

            foreach ($columns as $column) {
                self::assertTrue(Schema::hasColumn($table, $column), "{$table} missing {$column}");
            }
        }

        foreach ($this->requiredIndexes() as $table => $indexes) {
            foreach ($indexes as $index) {
                self::assertTrue($this->hasIndex($table, $index), "{$table} missing index {$index}");
            }
        }
    }

    public function testMigrationRollsBackCourseAccountTables(): void
    {
        $migration = $this->courseAccountMigration();

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
            'edu_courses' => [
                'id',
                'tenant_id',
                'campus_id',
                'code',
                'name',
                'category',
                'subject',
                'unit_minutes',
                'cover_url',
                'description',
                'status',
                'sort_order',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_teacher_courses' => [
                'id',
                'tenant_id',
                'campus_id',
                'course_id',
                'teacher_id',
                'status',
                'authorized_at',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lesson_packages' => [
                'id',
                'tenant_id',
                'campus_id',
                'course_id',
                'code',
                'name',
                'lesson_units',
                'bonus_units',
                'total_units',
                'list_price',
                'sale_price',
                'validity_days',
                'status',
                'sort_order',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_student_course_accounts' => [
                'id',
                'tenant_id',
                'campus_id',
                'student_id',
                'course_id',
                'purchased_units',
                'bonus_units',
                'consumed_units',
                'adjusted_units',
                'refunded_units',
                'frozen_units',
                'available_units',
                'status',
                'first_enrollment_id',
                'last_enrollment_id',
                'opened_at',
                'expires_at',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_enrollments' => [
                'id',
                'tenant_id',
                'campus_id',
                'enrollment_no',
                'student_id',
                'course_id',
                'lesson_package_id',
                'account_id',
                'student_name_snapshot',
                'course_name_snapshot',
                'package_name_snapshot',
                'package_lesson_units',
                'package_bonus_units',
                'total_units',
                'list_price',
                'deal_amount',
                'status',
                'enrolled_at',
                'confirmed_at',
                'materialized_at',
                'cancelled_at',
                'cancel_reason',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredIndexes(): array
    {
        return [
            'edu_courses' => [
                'uk_edu_courses_tenant_campus_code',
                'idx_edu_courses_tenant_campus_status',
                'idx_edu_courses_tenant_campus_name',
                'idx_edu_courses_deleted_at',
            ],
            'edu_teacher_courses' => [
                'uk_edu_teacher_courses_tenant_course_teacher',
                'idx_edu_teacher_courses_tenant_teacher_status',
                'idx_edu_teacher_courses_tenant_course_status',
                'idx_edu_teacher_courses_tenant_campus_status',
                'idx_edu_teacher_courses_deleted_at',
            ],
            'edu_lesson_packages' => [
                'uk_edu_lesson_packages_tenant_campus_code',
                'idx_edu_lesson_packages_tenant_course_status',
                'idx_edu_lesson_packages_tenant_campus_status',
                'idx_edu_lesson_packages_deleted_at',
            ],
            'edu_student_course_accounts' => [
                'uk_edu_student_course_accounts_tenant_student_course',
                'idx_edu_student_course_accounts_tenant_campus_status',
                'idx_edu_student_course_accounts_tenant_student',
                'idx_edu_student_course_accounts_tenant_course',
                'idx_edu_student_course_accounts_expires_at',
                'idx_edu_student_course_accounts_deleted_at',
            ],
            'edu_enrollments' => [
                'uk_edu_enrollments_tenant_enrollment_no',
                'idx_edu_enrollments_tenant_student_status',
                'idx_edu_enrollments_tenant_course_status',
                'idx_edu_enrollments_tenant_package',
                'idx_edu_enrollments_tenant_account',
                'idx_edu_enrollments_tenant_campus_enrolled',
                'idx_edu_enrollments_deleted_at',
            ],
        ];
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return $indexes !== [];
    }

    private function courseAccountMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010200_create_v1_course_account_tables.php';
    }
}
