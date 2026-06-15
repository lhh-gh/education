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

namespace HyperfTests\Unit\Education\Academic;

use App\Model\Education\Academic\EducationGuardian;
use App\Repository\Education\Academic\GuardianRepository;

/**
 * @internal
 * @coversNothing
 */
final class GuardianRepositoryTest extends AcademicTestCase
{
    public function testMobileUniquenessIsTenantScoped(): void
    {
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');

        EducationGuardian::query()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Guardian A',
            'mobile' => '13800000001',
        ]);
        EducationGuardian::query()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Guardian B',
            'mobile' => '13800000001',
        ]);

        $repository = make(GuardianRepository::class);

        self::assertTrue($repository->existsMobile((int) $tenantA->id, '13800000001'));
        self::assertFalse($repository->existsMobile((int) $tenantA->id, '13800000002'));
        self::assertTrue($repository->existsMobile((int) $tenantB->id, '13800000001'));
    }
}
