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

use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Repository\Education\Foundation\CampusRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class CampusRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    public function testFindInTenantNeverReturnsOtherTenantCampus(): void
    {
        $tenantA = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']);
        $tenantB = EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'tenant_b', 'status' => 'enabled']);
        $campus = EducationCampus::query()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Main Campus',
            'code' => 'main',
            'status' => 'enabled',
        ]);

        $repository = make(CampusRepository::class);

        self::assertNotNull($repository->findInTenant((int) $tenantA->id, (int) $campus->id));
        self::assertNull($repository->findInTenant((int) $tenantB->id, (int) $campus->id));
    }
}
