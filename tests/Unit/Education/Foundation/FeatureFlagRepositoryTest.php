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
use App\Repository\Education\Foundation\FeatureFlagRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class FeatureFlagRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationFeatureFlag::query()->whereRaw('1=1')->forceDelete();
    }

    public function testFindActiveByOwnerFeatureRespectsTimeWindow(): void
    {
        EducationFeatureFlag::query()->create([
            'owner_type' => 'system',
            'owner_key' => 'system',
            'feature_code' => 'future.feature',
            'feature_name' => 'Future',
            'enabled' => true,
            'effective_from' => '2026-06-13 00:00:00',
            'status' => 'enabled',
        ]);
        EducationFeatureFlag::query()->create([
            'owner_type' => 'system',
            'owner_key' => 'system',
            'feature_code' => 'expired.feature',
            'feature_name' => 'Expired',
            'enabled' => true,
            'effective_to' => '2026-06-11 23:59:59',
            'status' => 'enabled',
        ]);
        $active = EducationFeatureFlag::query()->create([
            'owner_type' => 'system',
            'owner_key' => 'system',
            'feature_code' => 'active.feature',
            'feature_name' => 'Active',
            'enabled' => true,
            'effective_from' => '2026-06-11 00:00:00',
            'effective_to' => '2026-06-13 00:00:00',
            'status' => 'enabled',
        ]);

        $repository = make(FeatureFlagRepository::class);

        self::assertNull($repository->findActiveByOwnerFeature('system', 'future.feature', '2026-06-12 12:00:00'));
        self::assertNull($repository->findActiveByOwnerFeature('system', 'expired.feature', '2026-06-12 12:00:00'));
        self::assertSame((int) $active->id, (int) $repository->findActiveByOwnerFeature('system', 'active.feature', '2026-06-12 12:00:00')?->id);
        self::assertTrue($repository->existsByOwnerFeature('system', 'active.feature'));
    }
}
