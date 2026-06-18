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

namespace HyperfTests\Unit\Education\Group;

use App\Model\Education\Group\EducationRiskAuditEvent;
use App\Service\Education\Group\ContractService;

/**
 * @internal
 * @coversNothing
 */
final class ContractServiceTest extends GroupTestCase
{
    public function testActiveContractAmountChangeWritesRiskEvent(): void
    {
        $tenant = $this->tenant('group_contract');
        $campus = $this->campus($tenant);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 7401);
        $service = make(ContractService::class);

        $contract = $service->save([
            'campus_id' => (int) $campus->id,
            'contract_no' => 'CT202606100001',
            'contract_type' => 'lease',
            'title' => 'Campus Lease',
            'counterparty_name' => 'Landlord',
            'amount_cents' => 12000000,
            'status' => 'active',
            'start_date' => '2026-06-10',
            'end_date' => '2027-06-09',
        ], $context);
        $service->save([
            'id' => $contract['contract_id'],
            'campus_id' => (int) $campus->id,
            'contract_no' => 'CT202606100001',
            'contract_type' => 'lease',
            'title' => 'Campus Lease',
            'counterparty_name' => 'Landlord',
            'amount_cents' => 13000000,
            'status' => 'active',
        ], $context);

        self::assertSame(1, EducationRiskAuditEvent::query()->where('event_type', 'contract_amount_changed')->count());
    }
}
