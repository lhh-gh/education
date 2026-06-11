<?php

declare(strict_types=1);

namespace HyperfTests\Unit\Education\Foundation;

use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Repository\Education\Foundation\CampusRepository;
use PHPUnit\Framework\TestCase;

final class CampusRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    public function test_find_in_tenant_never_returns_other_tenant_campus(): void
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
