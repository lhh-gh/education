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

namespace HyperfTests\Unit\Education\Operations;

use App\Model\Education\Operations\EducationRenewalAlert;
use App\Service\Education\Operations\RenewalAlertService;

/**
 * @internal
 * @coversNothing
 */
final class RenewalAlertServiceTest extends OperationsTestCase
{
    public function testLowBalanceAlertIsDeduped(): void
    {
        $tenant = $this->tenant('ops_renewal_dedupe');
        $campus = $this->campus($tenant);
        $this->accountFixture($tenant, $campus, ['available_units' => '1.00']);
        $service = make(RenewalAlertService::class);

        $service->scanAccounts((int) $tenant->id, 2.0);
        $service->scanAccounts((int) $tenant->id, 2.0);

        self::assertSame(1, EducationRenewalAlert::query()->where('tenant_id', $tenant->id)->where('alert_type', 'low_balance')->where('status', 'open')->count());
    }
}
