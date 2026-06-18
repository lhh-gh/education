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

use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Education\Operations\EducationRenewalAlert;
use App\Repository\Education\Operations\RenewalAlertRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class RenewalAlertRepositoryTest extends OperationsTestCase
{
    public function testPlatformContextWithoutTenantIdCanPageOpenAlerts(): void
    {
        $tenant = $this->tenant('ops_renewal_repo_platform');
        $campus = $this->campus($tenant);
        $alert = $this->createOpenAlert((int) $tenant->id, (int) $campus->id);
        $repository = make(RenewalAlertRepository::class);

        $page = $repository->pageOpen(['page' => 1, 'pageSize' => 20], new EducationUserContext(
            userId: 1,
            tenantId: null,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: null
        ));

        self::assertSame(1, $page['total']);
        self::assertSame($alert->id, $page['list'][0]['id']);
    }

    public function testNonPlatformContextWithoutTenantIdReturnsEmptyPage(): void
    {
        $tenant = $this->tenant('ops_renewal_repo_empty');
        $campus = $this->campus($tenant);
        $this->createOpenAlert((int) $tenant->id, (int) $campus->id);
        $repository = make(RenewalAlertRepository::class);

        $page = $repository->pageOpen(['page' => 1, 'pageSize' => 20], new EducationUserContext(
            userId: 1,
            tenantId: null,
            roleCode: EducationRoleCode::TenantAdmin,
            platformAccess: false,
            campusIds: [],
            currentCampusId: null
        ));

        self::assertSame(0, $page['total']);
        self::assertSame([], $page['list']);
    }

    private function createOpenAlert(int $tenantId, int $campusId): EducationRenewalAlert
    {
        return EducationRenewalAlert::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_id' => 1,
            'course_id' => 1,
            'student_course_account_id' => 1,
            'alert_type' => 'low_balance',
            'alert_level' => 'urgent',
            'status' => 'open',
            'trigger_value' => '1.00',
            'threshold_value' => '2.00',
            'due_date' => '2026-06-20',
        ]);
    }
}
