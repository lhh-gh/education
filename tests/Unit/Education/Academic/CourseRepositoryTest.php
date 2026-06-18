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

use App\Model\Education\Academic\EducationCourse;
use App\Repository\Education\Academic\CourseRepository;

/**
 * @internal
 * @coversNothing
 */
final class CourseRepositoryTest extends AcademicTestCase
{
    public function testPageFiltersByTenantCampusKeywordStatus(): void
    {
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $campusA = $this->campus($tenantA, 'main_a');
        $campusB = $this->campus($tenantB, 'main_b');

        $visible = EducationCourse::query()->create([
            'tenant_id' => $tenantA->id,
            'campus_id' => $campusA->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);
        EducationCourse::query()->create([
            'tenant_id' => $tenantA->id,
            'campus_id' => $campusA->id,
            'code' => 'ART-002',
            'name' => 'Art Disabled',
            'status' => 'disabled',
        ]);
        EducationCourse::query()->create([
            'tenant_id' => $tenantB->id,
            'campus_id' => $campusB->id,
            'code' => 'ART-001',
            'name' => 'Art Other Tenant',
            'status' => 'enabled',
        ]);

        $result = make(CourseRepository::class)->pageByContext([
            'campus_id' => $campusA->id,
            'keyword' => 'Art',
            'status' => 'enabled',
        ], 1, 20, $this->context((int) $tenantA->id));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testExistsCodeIgnoresDeletedRowsAndCurrentId(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $deleted = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'OLD',
            'name' => 'Deleted Course',
            'status' => 'enabled',
        ]);
        $deleted->delete();
        $active = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);

        $repository = make(CourseRepository::class);

        self::assertFalse($repository->existsCode((int) $tenant->id, (int) $campus->id, 'OLD'));
        self::assertTrue($repository->existsCode((int) $tenant->id, (int) $campus->id, 'ART-001'));
        self::assertFalse($repository->existsCode((int) $tenant->id, (int) $campus->id, 'ART-001', (int) $active->id));
    }
}
