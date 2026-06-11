<?php

declare(strict_types=1);

namespace HyperfTests\Unit\Education\Foundation;

use App\Http\Common\ResultCode;
use App\Exception\BusinessException;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Service\Education\Foundation\TenantService;
use PHPUnit\Framework\TestCase;

final class TenantServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    public function test_conflict_result_code_is_available_for_duplicate_tenant_failures(): void
    {
        self::assertSame(409, ResultCode::CONFLICT->value);
    }

    public function test_create_tenant_rejects_duplicate_code(): void
    {
        $service = make(TenantService::class);
        $service->createTenant(['name' => 'Demo Tenant', 'code' => 'demo']);

        try {
            $service->createTenant(['name' => 'Other Tenant', 'code' => 'demo']);
            self::fail('Expected duplicate tenant code to throw BusinessException.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function test_delete_tenant_rejects_tenant_with_campuses(): void
    {
        $service = make(TenantService::class);
        $tenant = $service->createTenant(['name' => 'Demo Tenant', 'code' => 'demo']);
        EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Campus',
            'code' => 'main',
            'status' => 'enabled',
        ]);

        $this->expectException(BusinessException::class);

        $service->deleteTenant((int) $tenant->id);
    }
}
