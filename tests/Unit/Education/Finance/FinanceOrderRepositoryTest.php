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

use App\Model\Education\Finance\EducationFinanceOrder;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Finance\FinanceOrderRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class FinanceOrderRepositoryTest extends FinanceTestCase
{
    public function testPlatformContextCampusFiltersOrderPageWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('fin_platform_scope');
        $campusA = $this->campus($tenant, 'scope_a');
        $campusB = $this->campus($tenant, 'scope_b');
        $orderA = $this->order((int) $tenant->id, (int) $campusA->id, 'FO-A');
        $this->order((int) $tenant->id, (int) $campusB->id, 'FO-B');

        $page = make(FinanceOrderRepository::class)->page([], new EducationUserContext(
            userId: 1,
            tenantId: (int) $tenant->id,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: (int) $campusA->id
        ));

        self::assertSame(1, $page['total']);
        self::assertSame((int) $orderA->id, (int) $page['list'][0]['id']);
    }

    private function order(int $tenantId, int $campusId, string $orderNo): EducationFinanceOrder
    {
        return EducationFinanceOrder::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'order_no' => $orderNo,
            'order_type' => 'enrollment',
            'student_id' => 1,
            'guardian_id' => 1,
            'enrollment_id' => 1,
            'student_course_account_id' => 1,
            'total_amount_cents' => 10000,
            'paid_amount_cents' => 0,
            'refund_amount_cents' => 0,
            'discount_amount_cents' => 0,
            'status' => 'pending',
        ]);
    }
}
