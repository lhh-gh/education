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
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;

/**
 * @internal
 * @coversNothing
 */
final class GuardianFinanceApiTest extends FinanceApiCase
{
    public function testGuardianReadsOnlyBoundStudentOrders(): void
    {
        $fixture = $this->financeFixture('finance_guardian_api');
        $this->createTenantProfile($fixture['tenant']);
        $this->grantPermissions('education:finance:order:create');
        $this->post('/admin/education/finance/orders/from-enrollment', [
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $this->tenantHeaders($fixture['tenant']));

        EducationUserProfile::query()
            ->where('tenant_id', $fixture['tenant']->id)
            ->where('user_id', $this->user->id)
            ->update(['role_code' => EducationRoleCode::Guardian->value, 'mobile' => $fixture['guardian']->mobile]);
        $bound = $this->get('/mobile/education/finance/guardian/orders', [
            'student_id' => $fixture['student']->id,
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $bound['code']);
        self::assertSame(1, $bound['data']['total']);

        $unbound = $this->get('/mobile/education/finance/guardian/orders', [
            'student_id' => $fixture['student']->id + 999,
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::FORBIDDEN->value, $unbound['code']);
    }
}
