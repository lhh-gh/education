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

use App\Service\Education\Payroll\SalaryRuleService;

/**
 * @internal
 * @coversNothing
 */
final class SalaryRuleServiceTest extends PayrollTestCase
{
    public function testRuleMatchingUsesPriorityAndEffectiveDate(): void
    {
        $tenant = $this->tenant('pay_rule');
        $campus = $this->campus($tenant);
        $teacher = $this->teacherFixture($tenant, $campus);
        $workload = $this->workloadFixture($tenant, $campus, $teacher);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9401);
        $service = make(SalaryRuleService::class);

        $service->save([
            'rule_code' => 'LOW',
            'rule_name' => 'Low priority',
            'effective_start' => '2026-01-01',
            'priority' => 1,
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 8000]],
        ], $context);
        $service->save([
            'rule_code' => 'HIGH',
            'rule_name' => 'High priority',
            'effective_start' => '2026-01-01',
            'priority' => 9,
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 12000]],
        ], $context);

        $matched = $service->matchForWorkload($workload, '2026-06', $context);

        self::assertSame('HIGH', $matched['rule_code']);
        self::assertSame(12000, $matched['unit_amount_cents']);
    }
}
