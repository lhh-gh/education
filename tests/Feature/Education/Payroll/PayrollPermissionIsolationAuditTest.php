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
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class PayrollPermissionIsolationAuditTest extends PayrollApiCase
{
    public function testPayrollMutationsRequirePermissionAndWriteAudit(): void
    {
        $fixture = $this->payrollFixture('pay_audit_api');
        $this->createTenantProfile($fixture['tenant']);

        $denied = $this->post('/admin/education/payroll/salary-rules', [
            'rule_code' => 'MAIN-001',
            'rule_name' => 'Main workload',
            'effective_start' => '2026-01-01',
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 12000]],
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);

        $this->grantPermissions('education:payroll:rule:create');
        $allowed = $this->post('/admin/education/payroll/salary-rules', [
            'rule_code' => 'MAIN-001',
            'rule_name' => 'Main workload',
            'effective_start' => '2026-01-01',
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 12000]],
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $allowed['code']);
        self::assertSame(1, EducationAuditLog::query()->where('module', 'payroll')->where('action', 'education.payroll.rule.saved')->count());
    }
}
