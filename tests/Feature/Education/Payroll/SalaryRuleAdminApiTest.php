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
final class SalaryRuleAdminApiTest extends PayrollApiCase
{
    public function testSalaryRuleApiValidationAndDuplicateFailureMatchCatalog(): void
    {
        $fixture = $this->payrollFixture('pay_rule_api');
        $this->createTenantProfile($fixture['tenant']);
        $this->grantPermissions('education:payroll:rule:create');

        $invalid = $this->post('/admin/education/payroll/salary-rules', [
            'rule_name' => 'Missing code',
            'effective_start' => '2026-06-01',
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 12000]],
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalid['code']);

        $payload = [
            'rule_code' => 'MAIN-001',
            'rule_name' => 'Main workload',
            'campus_id' => $fixture['campus']->id,
            'effective_start' => '2026-06-01',
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 12000]],
        ];
        $created = $this->post('/admin/education/payroll/salary-rules', $payload, $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame('enabled', $created['data']['status']);

        $duplicate = $this->post('/admin/education/payroll/salary-rules', $payload, $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::CONFLICT->value, $duplicate['code']);
    }
}
