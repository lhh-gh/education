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

namespace HyperfTests\Unit\Education\Payroll;

use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Operations\EducationTeacherWorkloadRecord;
use App\Model\Education\Payroll\EducationTeacherPerformanceMetric;
use App\Model\Education\Payroll\EducationTeacherSalaryAdjustment;
use App\Model\Education\Payroll\EducationTeacherSalaryBatch;
use App\Model\Education\Payroll\EducationTeacherSalaryItem;
use App\Model\Education\Payroll\EducationTeacherSalaryPayment;
use App\Model\Education\Payroll\EducationTeacherSalaryReview;
use App\Model\Education\Payroll\EducationTeacherSalaryRule;
use App\Model\Education\Payroll\EducationTeacherSalaryRuleItem;
use App\Model\Education\Payroll\EducationTeacherSalarySlip;
use App\Model\Education\Payroll\EducationTeacherWorkloadDispute;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Unit\Education\Academic\AcademicTestCase;

abstract class PayrollTestCase extends AcademicTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureOperationTables();
        $this->ensurePayrollTables();
        $this->cleanPayrollData();
    }

    protected function teacherFixture(EducationTenant $tenant, EducationCampus $campus, string $name = 'Payroll Teacher'): EducationTeacher
    {
        return EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'teacher_no' => 'T' . uniqid(),
            'name' => $name,
            'mobile' => '139' . random_int(10000000, 99999999),
            'gender' => 'unknown',
            'status' => 'enabled',
        ]);
    }

    protected function workloadFixture(
        EducationTenant $tenant,
        EducationCampus $campus,
        EducationTeacher $teacher,
        string $workloadType = 'main',
        string $recordedAt = '2026-06-10 10:00:00',
        string $credits = '2.00'
    ): EducationTeacherWorkloadRecord {
        return EducationTeacherWorkloadRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'teacher_id' => $teacher->id,
            'lesson_id' => random_int(10000, 99999),
            'workload_type' => $workloadType,
            'lesson_type' => 'normal',
            'credits' => $credits,
            'student_count' => 8,
            'present_count' => 7,
            'leave_count' => 1,
            'absent_count' => 0,
            'recorded_at' => $recordedAt,
        ]);
    }

    private function ensureOperationTables(): void
    {
        if (Schema::hasTable('edu_teacher_workload_records')) {
            return;
        }

        $migration = $this->operationMigration();
        $migration->down();
        $migration->up();
    }

    private function ensurePayrollTables(): void
    {
        if (Schema::hasTable('edu_teacher_salary_batches')) {
            return;
        }

        $migration = $this->payrollMigration();
        $migration->down();
        $migration->up();
    }

    private function cleanPayrollData(): void
    {
        EducationTeacherPerformanceMetric::query()->whereRaw('1 = 1')->delete();
        EducationTeacherWorkloadDispute::query()->forceDelete();
        EducationTeacherSalaryPayment::query()->whereRaw('1 = 1')->delete();
        EducationTeacherSalaryReview::query()->whereRaw('1 = 1')->delete();
        EducationTeacherSalaryAdjustment::query()->whereRaw('1 = 1')->delete();
        EducationTeacherSalaryItem::query()->whereRaw('1 = 1')->delete();
        EducationTeacherSalarySlip::query()->whereRaw('1 = 1')->delete();
        EducationTeacherSalaryBatch::query()->whereRaw('1 = 1')->delete();
        EducationTeacherSalaryRuleItem::query()->whereRaw('1 = 1')->delete();
        EducationTeacherSalaryRule::query()->forceDelete();
        EducationTeacherWorkloadRecord::query()->whereRaw('1 = 1')->delete();
    }

    private function operationMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_020000_create_v2_academic_operation_tables.php';
    }

    private function payrollMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_050000_create_v5_payroll_tables.php';
    }
}
