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

namespace HyperfTests\Feature\Education\Payroll;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class SalaryBatchAdminApiTest extends PayrollApiCase
{
    public function testCalculationAndReviewFailuresMatchCatalog(): void
    {
        $fixture = $this->payrollFixture('pay_batch_api');
        $this->createTenantProfile($fixture['tenant']);
        $this->grantPermissions('education:payroll:rule:create', 'education:payroll:batch:calculate', 'education:payroll:batch:approve');
        $this->post('/admin/education/payroll/salary-rules', [
            'rule_code' => 'MAIN-001',
            'rule_name' => 'Main workload',
            'campus_id' => $fixture['campus']->id,
            'effective_start' => '2026-01-01',
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 12000]],
        ], $this->tenantHeaders($fixture['tenant']));

        $invalid = $this->post('/admin/education/payroll/salary-batches/calculate', [
            'salary_month' => '202606',
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalid['code']);

        $calculated = $this->post('/admin/education/payroll/salary-batches/calculate', [
            'salary_month' => '2026-06',
            'campus_id' => $fixture['campus']->id,
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $calculated['code']);
        self::assertSame(1, $calculated['data']['teacher_count']);

        $approve = $this->post('/admin/education/payroll/salary-batches/' . $calculated['data']['batch_id'] . '/approve', [
            'review_note' => 'approved',
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::CONFLICT->value, $approve['code']);
        self::assertSame('salary batch is not submitted', $approve['message']);
    }
}
