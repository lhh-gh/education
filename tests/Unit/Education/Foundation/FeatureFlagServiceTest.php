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

use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Service\Education\Foundation\FeatureFlagService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class FeatureFlagServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationFeatureFlag::query()->whereRaw('1=1')->forceDelete();
    }

    public function testTenantFlagOverridesSystemDefault(): void
    {
        $this->createFlag('system', null, 'system', 'education.v2.academic_operations', false);
        $this->createFlag('tenant', 1, 'tenant:1', 'education.v2.academic_operations', true);

        $service = make(FeatureFlagService::class);

        self::assertTrue($service->enabled('education.v2.academic_operations', 1, '2026-06-12 12:00:00'));
        self::assertFalse($service->enabled('education.v2.academic_operations', 2, '2026-06-12 12:00:00'));
        self::assertSame('tenant:1', $service->resolved('education.v2.academic_operations', 1, '2026-06-12 12:00:00')['owner_key']);
    }

    public function testEffectiveWindowControlsFlag(): void
    {
        $this->createFlag('system', null, 'system', 'education.v3.admissions_crm', false);
        $this->createFlag('tenant', 1, 'tenant:1', 'education.v3.admissions_crm', true, '2026-06-13 00:00:00', null);

        $service = make(FeatureFlagService::class);

        self::assertFalse($service->enabled('education.v3.admissions_crm', 1, '2026-06-12 12:00:00'));
        self::assertTrue($service->enabled('education.v3.admissions_crm', 1, '2026-06-13 12:00:00'));
    }

    private function createFlag(
        string $ownerType,
        ?int $tenantId,
        string $ownerKey,
        string $featureCode,
        bool $enabled,
        ?string $effectiveFrom = null,
        ?string $effectiveTo = null
    ): void {
        EducationFeatureFlag::query()->create([
            'owner_type' => $ownerType,
            'tenant_id' => $tenantId,
            'owner_key' => $ownerKey,
            'feature_code' => $featureCode,
            'feature_name' => $featureCode,
            'enabled' => $enabled,
            'effective_from' => $effectiveFrom,
            'effective_to' => $effectiveTo,
            'status' => 'enabled',
        ]);
    }
}
