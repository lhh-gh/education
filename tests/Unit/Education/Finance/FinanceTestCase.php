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

namespace HyperfTests\Unit\Education\Finance;

use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Finance\EducationFinanceAdjustment;
use App\Model\Education\Finance\EducationFinanceOrder;
use App\Model\Education\Finance\EducationFinanceOrderItem;
use App\Model\Education\Finance\EducationPaymentCallback;
use App\Model\Education\Finance\EducationPaymentChannel;
use App\Model\Education\Finance\EducationPaymentRecord;
use App\Model\Education\Finance\EducationReceipt;
use App\Model\Education\Finance\EducationReconciliationBatch;
use App\Model\Education\Finance\EducationReconciliationItem;
use App\Model\Education\Finance\EducationRefundRecord;
use App\Model\Education\Finance\EducationRefundRequest;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Unit\Education\Academic\AcademicTestCase;

abstract class FinanceTestCase extends AcademicTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureFinanceTables();
        $this->cleanFinanceData();
    }

    protected function studentFixture(EducationTenant $tenant, EducationCampus $campus): EducationStudent
    {
        return EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_no' => 'STU' . uniqid(),
            'name' => 'Finance Student',
            'gender' => 'unknown',
            'status' => 'enabled',
        ]);
    }

    protected function guardianFixture(EducationTenant $tenant): EducationGuardian
    {
        return EducationGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Finance Guardian',
            'mobile' => '138' . random_int(10000000, 99999999),
            'status' => 'enabled',
        ]);
    }

    protected function packageFixture(EducationTenant $tenant, EducationCampus $campus): EducationLessonPackage
    {
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'FC' . uniqid(),
            'name' => 'Finance Course',
            'status' => 'enabled',
        ]);

        return EducationLessonPackage::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'FPKG' . uniqid(),
            'name' => 'Finance Package',
            'lesson_units' => '20.00',
            'bonus_units' => '4.00',
            'total_units' => '24.00',
            'list_price' => '3000.00',
            'sale_price' => '2400.00',
            'validity_days' => 365,
            'status' => 'enabled',
        ]);
    }

    protected function pendingEnrollmentFixture(EducationTenant $tenant, EducationCampus $campus, EducationStudent $student, EducationLessonPackage $package): EducationEnrollment
    {
        $course = EducationCourse::query()->findOrFail((int) $package->course_id);

        return EducationEnrollment::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'enrollment_no' => 'ENR' . uniqid(),
            'student_id' => $student->id,
            'course_id' => $course->id,
            'lesson_package_id' => $package->id,
            'student_name_snapshot' => $student->name,
            'course_name_snapshot' => $course->name,
            'package_name_snapshot' => $package->name,
            'package_lesson_units' => $package->lesson_units,
            'package_bonus_units' => $package->bonus_units,
            'total_units' => $package->total_units,
            'list_price' => $package->list_price,
            'deal_amount' => $package->sale_price,
            'status' => 'pending',
            'enrolled_at' => '2026-06-10 09:00:00',
        ]);
    }

    private function ensureFinanceTables(): void
    {
        foreach (['edu_finance_orders', 'edu_payment_records', 'edu_reconciliation_items'] as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }
            $migration = $this->financeMigration();
            $migration->down();
            $migration->up();

            return;
        }
    }

    private function cleanFinanceData(): void
    {
        EducationFinanceAdjustment::query()->whereRaw('1 = 1')->delete();
        EducationReconciliationItem::query()->whereRaw('1 = 1')->delete();
        EducationReconciliationBatch::query()->forceDelete();
        EducationReceipt::query()->forceDelete();
        EducationRefundRecord::query()->whereRaw('1 = 1')->delete();
        EducationRefundRequest::query()->forceDelete();
        EducationPaymentCallback::query()->whereRaw('1 = 1')->delete();
        EducationPaymentRecord::query()->whereRaw('1 = 1')->delete();
        EducationPaymentChannel::query()->forceDelete();
        EducationFinanceOrderItem::query()->whereRaw('1 = 1')->delete();
        EducationFinanceOrder::query()->forceDelete();
    }

    private function financeMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_040000_create_v4_finance_tables.php';
    }
}
