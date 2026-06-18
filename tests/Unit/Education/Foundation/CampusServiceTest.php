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

namespace HyperfTests\Unit\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Service\Education\Foundation\CampusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class CampusServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    public function testCreateCampusUsesContextTenantAndAllowsSameCodeInDifferentTenants(): void
    {
        $tenantA = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']);
        $tenantB = EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'tenant_b', 'status' => 'enabled']);
        $service = make(CampusService::class);

        $campusA = $service->createCampus((int) $tenantA->id, [
            'tenant_id' => $tenantB->id,
            'name' => 'Main Campus',
            'code' => 'main',
        ]);
        $campusB = $service->createCampus((int) $tenantB->id, [
            'name' => 'Main Campus',
            'code' => 'main',
        ]);

        self::assertSame((int) $tenantA->id, (int) $campusA->tenant_id);
        self::assertSame((int) $tenantB->id, (int) $campusB->tenant_id);
    }

    public function testDuplicateCampusCodeInSameTenantThrowsConflict(): void
    {
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $service = make(CampusService::class);
        $service->createCampus((int) $tenant->id, ['name' => 'Main Campus', 'code' => 'main']);

        try {
            $service->createCampus((int) $tenant->id, ['name' => 'Other Campus', 'code' => 'main']);
            self::fail('Expected duplicate campus code to throw BusinessException.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }
}
