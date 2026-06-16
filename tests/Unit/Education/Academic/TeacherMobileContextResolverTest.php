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
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Academic\TeacherMobileContextResolver;

/**
 * @internal
 * @coversNothing
 */
final class TeacherMobileContextResolverTest extends AcademicTestCase
{
    public function testResolvesEnabledTeacherFromMobileContext(): void
    {
        [$tenantId, $campusId, $profile] = $this->profileFixture();
        $teacher = $this->teacher($tenantId, $campusId, (int) $profile->id);

        $resolved = make(TeacherMobileContextResolver::class)->resolveTeacher(
            $this->context($tenantId, EducationRoleCode::Teacher, [$campusId], (int) $profile->user_id)
        );

        self::assertSame((int) $teacher->id, (int) $resolved->id);
    }

    public function testGuardianRoleIsRejected(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');

        try {
            make(TeacherMobileContextResolver::class)->assertTeacherRole(
                $this->context((int) $tenant->id, EducationRoleCode::Guardian, [(int) $campus->id], 9001)
            );
            self::fail('Expected teacher role guard to reject guardian context.');
        } catch (BusinessException $exception) {
            self::assertSame(403, $exception->getResponse()->code->value);
            self::assertSame('teacher mobile role required', $exception->getResponse()->message);
        }
    }

    public function testDisabledTeacherRecordIsRejected(): void
    {
        [$tenantId, $campusId, $profile] = $this->profileFixture();
        $this->teacher($tenantId, $campusId, (int) $profile->id, 'disabled');

        try {
            make(TeacherMobileContextResolver::class)->resolveTeacher(
                $this->context($tenantId, EducationRoleCode::Teacher, [$campusId], (int) $profile->user_id)
            );
            self::fail('Expected disabled teacher record to be rejected.');
        } catch (BusinessException $exception) {
            self::assertSame(403, $exception->getResponse()->code->value);
            self::assertSame('teacher profile is not enabled', $exception->getResponse()->message);
        }
    }

    public function testOutOfScopeCampusIsRejected(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $otherCampus = $this->campus($tenant, 'other');

        try {
            make(TeacherMobileContextResolver::class)->assertCampusAllowed(
                $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9001),
                (int) $otherCampus->id
            );
            self::fail('Expected out of scope campus to be rejected.');
        } catch (BusinessException $exception) {
            self::assertSame(403, $exception->getResponse()->code->value);
            self::assertSame('campus is outside teacher scope', $exception->getResponse()->message);
        }
    }

    private function profileFixture(): array
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'teacher-9001',
            'tenant_id' => $tenant->id,
            'user_id' => 9001,
            'role_code' => EducationRoleCode::Teacher->value,
            'display_name' => 'Teacher Mobile',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);

        return [(int) $tenant->id, (int) $campus->id, $profile];
    }

    private function teacher(int $tenantId, int $campusId, int $profileId, string $status = 'enabled'): EducationTeacher
    {
        return EducationTeacher::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'user_profile_id' => $profileId,
            'teacher_no' => uniqid('T', false),
            'name' => 'Teacher Mobile',
            'status' => $status,
        ]);
    }
}
