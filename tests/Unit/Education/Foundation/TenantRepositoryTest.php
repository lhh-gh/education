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
use App\Repository\Education\Foundation\TenantRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class TenantRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    public function testExistsByCodeCanIgnoreCurrentTenant(): void
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
