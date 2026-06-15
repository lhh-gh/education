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
final class ProfileRecordMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->profileRecordMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testProfileRecordTablesHaveRequiredColumnsAndIndexes(): void
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

    public function testMigrationRollsBackProfileRecordTables(): void
    {
        $migration = $this->profileRecordMigration();

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
            'edu_classrooms' => [
                'id',
                'tenant_id',
                'campus_id',
                'code',
                'name',
                'capacity',
                'location',
                'equipment',
                'status',
                'sort_order',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_students' => [
                'id',
                'tenant_id',
                'campus_id',
                'student_no',
                'name',
                'gender',
                'birthday',
                'mobile',
                'school',
                'grade',
                'source',
                'avatar',
                'enrolled_at',
                'status',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_guardians' => [
                'id',
                'tenant_id',
                'name',
                'mobile',
                'gender',
                'openid',
                'unionid',
                'status',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_student_guardians' => [
                'id',
                'tenant_id',
                'student_id',
                'guardian_id',
                'relation',
                'is_primary',
                'can_receive_notice',
                'can_submit_leave',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_teachers' => [
                'id',
                'tenant_id',
                'campus_id',
                'user_profile_id',
                'teacher_no',
                'name',
                'mobile',
                'gender',
                'birthday',
                'title',
                'hire_date',
                'avatar',
                'introduction',
                'status',
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
            'edu_classrooms' => [
                'uk_edu_classrooms_tenant_campus_code',
                'idx_edu_classrooms_tenant_campus_status',
                'idx_edu_classrooms_tenant_campus_name',
                'idx_edu_classrooms_deleted_at',
            ],
            'edu_students' => [
                'uk_edu_students_tenant_student_no',
                'idx_edu_students_tenant_campus_status',
                'idx_edu_students_tenant_name_mobile',
                'idx_edu_students_tenant_campus_name',
                'idx_edu_students_deleted_at',
            ],
            'edu_guardians' => [
                'uk_edu_guardians_tenant_mobile',
                'uk_edu_guardians_tenant_openid',
                'uk_edu_guardians_tenant_unionid',
                'idx_edu_guardians_tenant_status',
                'idx_edu_guardians_tenant_name_mobile',
                'idx_edu_guardians_deleted_at',
            ],
            'edu_student_guardians' => [
                'uk_edu_student_guardians_tenant_student_guardian',
                'idx_edu_student_guardians_tenant_student',
                'idx_edu_student_guardians_tenant_guardian',
                'idx_edu_student_guardians_tenant_relation',
                'idx_edu_student_guardians_deleted_at',
            ],
            'edu_teachers' => [
                'uk_edu_teachers_tenant_teacher_no',
                'uk_edu_teachers_user_profile',
                'idx_edu_teachers_tenant_campus_status',
                'idx_edu_teachers_tenant_name_mobile',
                'idx_edu_teachers_deleted_at',
            ],
        ];
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return $indexes !== [];
    }

    private function profileRecordMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010100_create_v1_profile_record_tables.php';
    }
}
