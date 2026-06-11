<?php

declare(strict_types=1);

namespace HyperfTests\Unit\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Service\Education\Foundation\CampusService;
use PHPUnit\Framework\TestCase;

final class CampusServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    public function test_create_campus_uses_context_tenant_and_allows_same_code_in_different_tenants(): void
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

    public function test_duplicate_campus_code_in_same_tenant_throws_conflict(): void
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
