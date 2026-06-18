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
use App\Service\Education\Foundation\TenantService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class TenantServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    public function testConflictResultCodeIsAvailableForDuplicateTenantFailures(): void
    {
        self::assertSame(409, ResultCode::CONFLICT->value);
    }

    public function testCreateTenantRejectsDuplicateCode(): void
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

    public function testDeleteTenantRejectsTenantWithCampuses(): void
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
