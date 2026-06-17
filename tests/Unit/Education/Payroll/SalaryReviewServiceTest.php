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

use App\Model\Education\Payroll\EducationTeacherSalaryAdjustment;
use App\Model\Education\Payroll\EducationTeacherSalaryItem;
use App\Model\Education\Payroll\EducationTeacherSalarySlip;
use App\Service\Education\Payroll\SalaryCalculationService;
use App\Service\Education\Payroll\SalaryReviewService;
use App\Service\Education\Payroll\SalaryRuleService;

/**
 * @internal
 * @coversNothing
 */
final class SalaryReviewServiceTest extends PayrollTestCase
{
    public function testAdjustmentCreatesSeparateItem(): void
    {
        $tenant = $this->tenant('pay_adjust');
        $campus = $this->campus($tenant);
        $teacher = $this->teacherFixture($tenant, $campus);
        $workload = $this->workloadFixture($tenant, $campus, $teacher);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9404);
        make(SalaryRuleService::class)->save([
            'rule_code' => 'MAIN',
            'rule_name' => 'Main workload',
            'effective_start' => '2026-01-01',
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 10000]],
        ], $context);
        $batch = make(SalaryCalculationService::class)->calculate(['salary_month' => '2026-06'], $context);
        make(SalaryReviewService::class)->approveBatch((int) $batch['batch_id'], ['review_note' => 'approved'], $context);
        $slip = EducationTeacherSalarySlip::query()->where('teacher_id', $teacher->id)->first();

        $result = make(SalaryReviewService::class)->adjustSlip((int) $slip->id, [
            'adjustment_type' => 'bonus',
            'amount_cents' => 5000,
            'reason' => 'excellent service',
        ], $context);

        self::assertSame(25000, $result['payable_amount_cents']);
        self::assertSame(1, EducationTeacherSalaryAdjustment::query()->where('salary_slip_id', $slip->id)->count());
        self::assertSame(1, EducationTeacherSalaryItem::query()->where('salary_slip_id', $slip->id)->where('item_type', 'adjustment')->count());
        self::assertSame('2.00', EducationTeacherSalaryItem::query()->where('source_workload_id', $workload->id)->first()->snapshot_json['credits']);
    }
}
