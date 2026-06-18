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
use App\Model\Education\Payroll\EducationTeacherSalarySlip;

/**
 * @internal
 * @coversNothing
 */
final class SalaryPaymentAdminApiTest extends PayrollApiCase
{
    public function testPaymentMarkRequiresApprovedSlip(): void
    {
        $fixture = $this->payrollFixture('pay_payment_api');
        $this->createTenantProfile($fixture['tenant']);
        $this->grantPermissions('education:payroll:rule:create', 'education:payroll:batch:calculate', 'education:payroll:payment:mark');
        $this->post('/admin/education/payroll/salary-rules', [
            'rule_code' => 'MAIN-001',
            'rule_name' => 'Main workload',
            'campus_id' => $fixture['campus']->id,
            'effective_start' => '2026-01-01',
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 12000]],
        ], $this->tenantHeaders($fixture['tenant']));
        $batch = $this->post('/admin/education/payroll/salary-batches/calculate', [
            'salary_month' => '2026-06',
            'campus_id' => $fixture['campus']->id,
        ], $this->tenantHeaders($fixture['tenant']));

        $slipId = EducationTeacherSalarySlip::query()->where('batch_id', $batch['data']['batch_id'])->value('id');
        $payment = $this->post('/admin/education/payroll/salary-payments', [
            'salary_slip_id' => $slipId,
            'payment_no' => 'SP202606100001',
            'paid_amount_cents' => 24000,
            'payment_method' => 'bank_transfer',
            'paid_at' => '2026-07-05 10:00:00',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::CONFLICT->value, $payment['code']);
        self::assertSame('salary slip is not approved', $payment['message']);
    }
}
