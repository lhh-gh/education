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

use App\Service\Education\Group\RiskAuditService;

/**
 * @internal
 * @coversNothing
 */
final class RiskAuditServiceTest extends GroupTestCase
{
    public function testRiskAuditQueryRespectsDataScope(): void
    {
        $tenant = $this->tenant('group_risk');
        $campusA = $this->campus($tenant, 'visible');
        $campusB = $this->campus($tenant, 'hidden');
        $service = make(RiskAuditService::class);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campusA->id], userId: 7501);

        $service->record([
            'campus_id' => (int) $campusA->id,
            'event_type' => 'visible',
            'risk_level' => 'high',
            'business_type' => 'contract',
            'business_id' => 1,
            'summary' => 'Visible event',
            'payload_json' => [],
        ], $context);
        $service->record([
            'campus_id' => (int) $campusB->id,
            'event_type' => 'hidden',
            'risk_level' => 'high',
            'business_type' => 'contract',
            'business_id' => 2,
            'summary' => 'Hidden event',
            'payload_json' => [],
        ], $this->context((int) $tenant->id, campusIds: [(int) $campusB->id], userId: 7502));

        $page = $service->page(['risk_level' => 'high'], $context);

        self::assertSame(1, $page['total']);
        self::assertSame('visible', $page['list'][0]['event_type']);
    }
}
