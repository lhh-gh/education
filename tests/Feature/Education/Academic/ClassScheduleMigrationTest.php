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
final class ClassScheduleMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->classScheduleMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testClassScheduleTablesExist(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasTable($table), "{$table} table missing");
        }
    }

    public function testClassColumnsAndIndexesExist(): void
    {
        foreach ($this->requiredColumns()['edu_classes'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_classes', $column), "edu_classes missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_classes', 'uk_edu_classes_tenant_campus_code'));
    }

    public function testClassStudentAccountColumnsExist(): void
    {
        foreach (['account_id', 'student_name_snapshot', 'student_no_snapshot', 'joined_at', 'left_at'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_class_students', $column), "edu_class_students missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_class_students', 'uk_edu_class_students_tenant_class_student'));
    }

    public function testLessonTimeConflictIndexesExist(): void
    {
        foreach ([
            'idx_edu_lessons_tenant_campus_time',
            'idx_edu_lessons_tenant_teacher_time',
            'idx_edu_lessons_tenant_classroom_time',
            'idx_edu_lessons_tenant_class_time',
        ] as $index) {
            self::assertTrue($this->hasIndex('edu_lessons', $index), "edu_lessons missing index {$index}");
        }
    }

    public function testLessonStudentSnapshotColumnsExist(): void
    {
        foreach ($this->requiredColumns()['edu_lesson_students'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_lesson_students', $column), "edu_lesson_students missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_lesson_students', 'uk_edu_lesson_students_tenant_lesson_student'));
    }

    public function testRollbackDropsTablesInDependencyOrder(): void
    {
        $migration = $this->classScheduleMigration();

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
            'edu_classes' => [
                'id',
                'tenant_id',
                'campus_id',
                'course_id',
                'main_teacher_id',
                'classroom_id',
                'code',
                'name',
                'class_type',
                'max_students',
                'start_date',
                'end_date',
                'lesson_units',
                'status',
                'schedule_note',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_class_students' => [
                'id',
                'tenant_id',
                'campus_id',
                'class_id',
                'course_id',
                'student_id',
                'account_id',
                'student_name_snapshot',
                'student_no_snapshot',
                'status',
                'joined_at',
                'left_at',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lessons' => [
                'id',
                'tenant_id',
                'campus_id',
                'lesson_no',
                'class_id',
                'course_id',
                'teacher_id',
                'classroom_id',
                'title',
                'start_at',
                'end_at',
                'duration_minutes',
                'lesson_units',
                'student_count',
                'status',
                'source_type',
                'schedule_batch_no',
                'class_name_snapshot',
                'course_name_snapshot',
                'teacher_name_snapshot',
                'classroom_name_snapshot',
                'cancelled_at',
                'cancel_reason',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lesson_students' => [
                'id',
                'tenant_id',
                'campus_id',
                'lesson_id',
                'class_id',
                'course_id',
                'student_id',
                'account_id',
                'student_name_snapshot',
                'student_no_snapshot',
                'lesson_units',
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

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return $indexes !== [];
    }

    private function classScheduleMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_010300_create_v1_class_lesson_tables.php';
    }
}
