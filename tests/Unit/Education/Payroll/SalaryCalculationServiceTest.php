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

use App\Exception\BusinessException;
use App\Model\Education\Operations\EducationTeacherWorkloadRecord;
use App\Model\Education\Payroll\EducationTeacherSalaryItem;
use App\Model\Education\Payroll\EducationTeacherSalarySlip;
use App\Service\Education\Payroll\SalaryCalculationService;
use App\Service\Education\Payroll\SalaryReviewService;
use App\Service\Education\Payroll\SalaryRuleService;

/**
 * @internal
 * @coversNothing
 */
final class SalaryCalculationServiceTest extends PayrollTestCase
{
    public function testBatchCreatesImmutableWorkloadSnapshot(): void
    {
        $tenant = $this->tenant('pay_calc');
        $campus = $this->campus($tenant);
        $teacher = $this->teacherFixture($tenant, $campus);
        $workload = $this->workloadFixture($tenant, $campus, $teacher, credits: '2.50');
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9402);
        make(SalaryRuleService::class)->save([
            'rule_code' => 'MAIN',
            'rule_name' => 'Main workload',
            'effective_start' => '2026-01-01',
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 10000]],
        ], $context);

        $batch = make(SalaryCalculationService::class)->calculate([
            'salary_month' => '2026-06',
            'campus_id' => (int) $campus->id,
        ], $context);
        $workload->fill(['credits' => '9.00'])->save();
        $item = EducationTeacherSalaryItem::query()->where('source_workload_id', $workload->id)->first();
        $slip = EducationTeacherSalarySlip::query()->where('batch_id', $batch['batch_id'])->first();

        self::assertSame(25000, (int) $item->amount_cents);
        self::assertSame('2.50', number_format((float) $item->quantity, 2, '.', ''));
        self::assertSame('2.50', $item->snapshot_json['credits']);
        self::assertSame('9.00', EducationTeacherWorkloadRecord::query()->find($workload->id)->credits);
        self::assertSame(25000, (int) $slip->payable_amount_cents);
    }

    public function testApprovedBatchCannotBeRebuilt(): void
    {
        $tenant = $this->tenant('pay_rebuild');
        $campus = $this->campus($tenant);
        $teacher = $this->teacherFixture($tenant, $campus);
        $this->workloadFixture($tenant, $campus, $teacher);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9403);
        make(SalaryRuleService::class)->save([
            'rule_code' => 'MAIN',
            'rule_name' => 'Main workload',
            'effective_start' => '2026-01-01',
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 10000]],
        ], $context);
        $batch = make(SalaryCalculationService::class)->calculate(['salary_month' => '2026-06'], $context);
        make(SalaryReviewService::class)->approveBatch((int) $batch['batch_id'], ['review_note' => 'approved'], $context);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('approved salary batch cannot be rebuilt');
        make(SalaryCalculationService::class)->rebuild((int) $batch['batch_id'], $context);
    }
}
