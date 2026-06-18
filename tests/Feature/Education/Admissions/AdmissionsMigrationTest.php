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

namespace HyperfTests\Feature\Education\Admissions;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class AdmissionsMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys($this->requiredColumns()) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            $migration = $this->admissionsMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    public function testAdmissionsTablesExistWithIndexes(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasTable($table), "{$table} table missing");
        }

        foreach ($this->requiredIndexes() as $table => $indexes) {
            foreach ($indexes as $index) {
                self::assertTrue($this->hasIndex($table, $index), "{$table} missing index {$index}");
            }
        }
    }

    public function testAdmissionsTablesHaveTenantAndCampusColumns(): void
    {
        foreach (array_keys($this->requiredColumns()) as $table) {
            self::assertTrue(Schema::hasColumn($table, 'tenant_id'), "{$table} missing tenant_id");
            self::assertTrue(Schema::hasColumn($table, 'campus_id'), "{$table} missing campus_id");
        }
    }

    public function testAdmissionsColumnsMatchPlan(): void
    {
        foreach ($this->requiredColumns() as $table => $columns) {
            foreach ($columns as $column) {
                self::assertTrue(Schema::hasColumn($table, $column), "{$table} missing {$column}");
            }
        }
    }

    public function testJsonColumnsExist(): void
    {
        self::assertSame('json', $this->columnType('edu_lead_conversion_records', 'payload_json'));
    }

    public function testRollbackDropsAdmissionsTablesInReverseOrder(): void
    {
        $migration = $this->admissionsMigration();

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
            'edu_lead_sources' => [
                'id',
                'tenant_id',
                'campus_id',
                'code',
                'name',
                'channel_type',
                'default_consultant_id',
                'status',
                'sort_order',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_leads' => [
                'id',
                'tenant_id',
                'campus_id',
                'lead_no',
                'source_id',
                'contact_name',
                'contact_mobile',
                'contact_wechat',
                'stage',
                'status',
                'owner_user_id',
                'intention_course_id',
                'intention_level',
                'next_follow_at',
                'last_follow_at',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lead_guardians' => [
                'id',
                'tenant_id',
                'campus_id',
                'lead_id',
                'name',
                'mobile',
                'relation',
                'wechat',
                'is_primary',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lead_students' => [
                'id',
                'tenant_id',
                'campus_id',
                'lead_id',
                'name',
                'gender',
                'birthday',
                'grade',
                'school',
                'intention_course_id',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lead_assignments' => [
                'id',
                'tenant_id',
                'campus_id',
                'lead_id',
                'from_user_id',
                'to_user_id',
                'status',
                'assigned_at',
                'reason',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_lead_follow_records' => [
                'id',
                'tenant_id',
                'campus_id',
                'lead_id',
                'follow_type',
                'content',
                'next_follow_at',
                'result',
                'operator_user_id',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ],
            'edu_trial_lessons' => [
                'id',
                'tenant_id',
                'campus_id',
                'lead_id',
                'lead_student_id',
                'course_id',
                'teacher_id',
                'classroom_id',
                'start_time',
                'end_time',
                'status',
                'consultant_user_id',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_trial_attendances' => [
                'id',
                'tenant_id',
                'campus_id',
                'trial_lesson_id',
                'lead_student_id',
                'attendance_status',
                'checked_by',
                'checked_at',
                'remark',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ],
            'edu_trial_feedbacks' => [
                'id',
                'tenant_id',
                'campus_id',
                'trial_lesson_id',
                'lead_id',
                'feedback_type',
                'teacher_id',
                'consultant_user_id',
                'score',
                'content',
                'recommend_course_id',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ],
            'edu_lead_conversion_records' => [
                'id',
                'tenant_id',
                'campus_id',
                'lead_id',
                'student_id',
                'guardian_id',
                'enrollment_id',
                'student_course_account_id',
                'status',
                'converted_by',
                'converted_at',
                'payload_json',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ],
            'edu_admission_tasks' => [
                'id',
                'tenant_id',
                'campus_id',
                'lead_id',
                'task_type',
                'title',
                'assignee_user_id',
                'status',
                'due_at',
                'completed_at',
                'result',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'edu_admission_metrics_daily' => [
                'id',
                'tenant_id',
                'campus_id',
                'metric_date',
                'source_id',
                'consultant_user_id',
                'new_leads_count',
                'follow_count',
                'trial_count',
                'trial_attended_count',
                'converted_count',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredIndexes(): array
    {
        return [
            'edu_lead_sources' => [
                'uk_edu_lead_sources_tenant_code',
                'idx_edu_lead_sources_status',
                'idx_edu_lead_sources_deleted_at',
            ],
            'edu_leads' => [
                'uk_edu_leads_tenant_lead_no',
                'idx_edu_leads_tenant_mobile',
                'idx_edu_leads_stage_owner',
                'idx_edu_leads_next_follow',
                'idx_edu_leads_deleted_at',
            ],
            'edu_lead_guardians' => [
                'idx_edu_lead_guardians_lead',
                'idx_edu_lead_guardians_mobile',
                'idx_edu_lead_guardians_deleted_at',
            ],
            'edu_lead_students' => [
                'idx_edu_lead_students_lead',
                'idx_edu_lead_students_name',
                'idx_edu_lead_students_deleted_at',
            ],
            'edu_lead_assignments' => [
                'idx_edu_lead_assignments_lead',
                'idx_edu_lead_assignments_owner',
                'idx_edu_lead_assignments_deleted_at',
            ],
            'edu_lead_follow_records' => [
                'idx_edu_lead_follow_records_lead_time',
                'idx_edu_lead_follow_records_operator',
            ],
            'edu_trial_lessons' => [
                'idx_edu_trial_lessons_time',
                'idx_edu_trial_lessons_teacher_time',
                'idx_edu_trial_lessons_lead',
                'idx_edu_trial_lessons_deleted_at',
            ],
            'edu_trial_attendances' => [
                'uk_edu_trial_attendances_lesson_student',
                'idx_edu_trial_attendances_status',
            ],
            'edu_trial_feedbacks' => [
                'idx_edu_trial_feedbacks_lesson',
                'idx_edu_trial_feedbacks_lead',
            ],
            'edu_lead_conversion_records' => [
                'uk_edu_lead_conversion_records_lead',
                'idx_edu_lead_conversion_records_student',
            ],
            'edu_admission_tasks' => [
                'idx_edu_admission_tasks_owner_due',
                'idx_edu_admission_tasks_lead',
                'idx_edu_admission_tasks_deleted_at',
            ],
            'edu_admission_metrics_daily' => [
                'uk_edu_admission_metrics_daily_scope',
                'idx_edu_admission_metrics_daily_date',
            ],
        ];
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return $indexes !== [];
    }

    private function columnType(string $table, string $column): string
    {
        $rows = Schema::getConnection()->select(
            'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        self::assertNotEmpty($rows, "{$table}.{$column} column metadata missing");

        return (string) $rows[0]->DATA_TYPE;
    }

    private function admissionsMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_030000_create_v3_admissions_tables.php';
    }
}
