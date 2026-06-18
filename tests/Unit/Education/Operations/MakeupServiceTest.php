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

use App\Model\Education\Operations\EducationMakeupEntitlement;
use App\Service\Education\Operations\MakeupService;

/**
 * @internal
 * @coversNothing
 */
final class MakeupServiceTest extends OperationsTestCase
{
    public function testApprovedLeaveCreatesOneEntitlement(): void
    {
        $tenant = $this->tenant('ops_makeup_once');
        $campus = $this->campus($tenant);
        $lesson = $this->lessonFixture($tenant, $campus);
        $leave = $this->leaveFixture($tenant, $campus, $lesson);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id]);
        $service = make(MakeupService::class);

        $first = $service->createEntitlementFromLeave((int) $leave->id, $context);
        $second = $service->createEntitlementFromLeave((int) $leave->id, $context);

        self::assertSame($first['id'], $second['id']);
        self::assertSame(1, EducationMakeupEntitlement::query()->where('tenant_id', $tenant->id)->count());
    }

    public function testMakeupCompletionMarksEntitlementUsedOnce(): void
    {
        $tenant = $this->tenant('ops_makeup_used');
        $campus = $this->campus($tenant);
        $lesson = $this->lessonFixture($tenant, $campus);
        $leave = $this->leaveFixture($tenant, $campus, $lesson);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id]);
        $service = make(MakeupService::class);
        $entitlement = $service->createEntitlementFromLeave((int) $leave->id, $context);
        $record = $service->arrangeMakeup([
            'makeup_entitlement_id' => (int) $entitlement['id'],
            'makeup_lesson_id' => (int) $lesson->id,
            'arranged_at' => '2026-06-12 10:00:00',
        ], $context);

        $first = $service->completeMakeupAttendance((int) $record['makeup_record_id'], $context);
        $second = $service->completeMakeupAttendance((int) $record['makeup_record_id'], $context);

        self::assertSame('used', $first['entitlement_status']);
        self::assertSame('used', $second['entitlement_status']);
        self::assertSame('used', EducationMakeupEntitlement::query()->find($entitlement['id'])->status);
    }
}
