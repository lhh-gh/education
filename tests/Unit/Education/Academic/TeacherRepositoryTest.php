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
use App\Repository\Education\Academic\TeacherRepository;

/**
 * @internal
 * @coversNothing
 */
final class TeacherRepositoryTest extends AcademicTestCase
{
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
