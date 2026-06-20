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

use App\Model\Education\Academic\EducationTeacher;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\TeacherRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class TeacherRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersPageWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('teacher_platform_scope');
        $campusA = $this->campus($tenant, 'scope_a');
        $campusB = $this->campus($tenant, 'scope_b');
        $visible = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusA->id,
            'teacher_no' => 'T-A',
            'name' => 'Teacher A',
        ]);
        EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusB->id,
            'teacher_no' => 'T-B',
            'name' => 'Teacher B',
        ]);

        $result = make(TeacherRepository::class)->pageByContext([], 1, 20, new EducationUserContext(
            userId: 1,
            tenantId: (int) $tenant->id,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: (int) $campusA->id
        ));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testUserProfileUniquenessExcludesCurrentRow(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'user_profile_id' => 5001,
            'teacher_no' => 'T001',
            'name' => 'Teacher A',
        ]);

        $repository = make(TeacherRepository::class);

        self::assertTrue($repository->existsUserProfile(5001));
        self::assertFalse($repository->existsUserProfile(5001, (int) $teacher->id));
        self::assertFalse($repository->existsUserProfile(5002));
    }
}
