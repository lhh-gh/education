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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Permission\User;
use App\Service\Education\Academic\TeacherService;

/**
 * @internal
 * @coversNothing
 */
final class TeacherServiceTest extends AcademicTestCase
{
    public function testTeacherProfileMustHaveTeacherRole(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $user = User::query()->create(['username' => 'edu_ac_gprof']);
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role_code' => 'guardian',
            'display_name' => 'Guardian Profile',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);

        try {
            make(TeacherService::class)->create([
                'tenant_id' => $tenant->id,
                'campus_id' => $campus->id,
                'user_profile_id' => $profile->id,
                'teacher_no' => 'T001',
                'name' => 'Teacher A',
            ], $this->context((int) $tenant->id), 901);
            self::fail('Expected non-teacher profile to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }
    }

    public function testDuplicateTeacherNumberReturnsConflict(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $service = make(TeacherService::class);
        $service->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'teacher_no' => 'T001',
            'name' => 'Teacher A',
        ], $this->context((int) $tenant->id), 901);

        try {
            $service->create([
                'tenant_id' => $tenant->id,
                'campus_id' => $campus->id,
                'teacher_no' => 'T001',
                'name' => 'Teacher B',
            ], $this->context((int) $tenant->id), 901);
            self::fail('Expected duplicate teacher number to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame('T001', $exception->getResponse()->data['teacher_no']);
        }
    }
}
