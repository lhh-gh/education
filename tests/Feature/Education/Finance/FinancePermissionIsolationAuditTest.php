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

namespace HyperfTests\Feature\Education\Finance;

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class FinancePermissionIsolationAuditTest extends FinanceApiCase
{
    public function testFinanceMutationsRequirePermissionAndWriteAudit(): void
    {
        $fixture = $this->financeFixture('finance_audit_api');
        $this->createTenantProfile($fixture['tenant']);
        $payload = [
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ];

        $denied = $this->post('/admin/education/finance/orders/from-enrollment', $payload, $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);

        $this->grantPermissions('education:finance:order:create');
        $allowed = $this->post('/admin/education/finance/orders/from-enrollment', $payload, $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $allowed['code']);
        self::assertTrue(EducationAuditLog::query()->where('action', 'education.finance.order.created')->exists());
    }
}
