<?php

declare(strict_types=1);

namespace HyperfTests\Unit\Education\Foundation;

use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Repository\Education\Foundation\TenantRepository;
use PHPUnit\Framework\TestCase;

final class TenantRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    public function test_exists_by_code_can_ignore_current_tenant(): void
    {
        $tenant = EducationTenant::query()->create([
            'name' => 'Demo Tenant',
            'code' => 'demo',
            'status' => 'enabled',
        ]);

        $repository = make(TenantRepository::class);

        self::assertTrue($repository->existsByCode('demo'));
        self::assertFalse($repository->existsByCode('demo', (int) $tenant->id));
    }
}
