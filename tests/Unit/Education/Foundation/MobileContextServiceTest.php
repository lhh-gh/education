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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Permission\User;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Foundation\MobileContextService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class MobileContextServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationFeatureFlag::query()->whereRaw('1=1')->forceDelete();
        EducationUserCampusScope::query()->delete();
        EducationUserProfile::query()->forceDelete();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
        User::query()->where('username', 'like', 'edum_%')->delete();
    }

    public function testTeacherContextRequiresTeacherRole(): void
    {
        try {
            $this->service()->teacherContext($this->context(1, 1, EducationRoleCode::Guardian), []);
            self::fail('Expected guardian role to be rejected by teacher context.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::FORBIDDEN, $exception->getResponse()->code);
            self::assertSame('guardian', $exception->getResponse()->data['role_code']);
        }
    }

    public function testTeacherContextRequiresCampusScope(): void
    {
        [$tenant, $user] = $this->tenantUserProfile('teacher');

        try {
            $this->service()->teacherContext($this->context((int) $user->id, (int) $tenant->id, EducationRoleCode::Teacher), []);
            self::fail('Expected teacher without campus scope to be rejected.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::FORBIDDEN, $exception->getResponse()->code);
            self::assertSame('teacher campus scope is required', $exception->getResponse()->message);
        }
    }

    public function testGuardianContextReturnsEmptyStudentsUntilV1(): void
    {
        [$tenant, $user] = $this->tenantUserProfile('guardian');

        $data = $this->service()->guardianContext($this->context((int) $user->id, (int) $tenant->id, EducationRoleCode::Guardian), []);

        self::assertSame([], $data['bound_students']);
        self::assertSame('guardian_students_pending_v1', $data['empty_state']['code']);
        self::assertSame('/pages/guardian/index', $data['entry']['default_path']);
    }

    public function testOperatorContextAcceptsFrontDesk(): void
    {
        [$tenant, $user, $profile] = $this->tenantUserProfile('front_desk');
        $campus = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Front Campus',
            'code' => 'front',
            'status' => 'enabled',
        ]);
        EducationUserCampusScope::query()->create([
            'tenant_id' => $tenant->id,
            'user_profile_id' => $profile->id,
            'user_id' => $user->id,
            'campus_id' => $campus->id,
        ]);

        $data = $this->service()->operatorContext($this->context((int) $user->id, (int) $tenant->id, EducationRoleCode::FrontDesk), []);

        self::assertSame('front_desk', $data['profile']['role_code']);
        self::assertSame('/pages/operator/index', $data['entry']['default_path']);
        self::assertSame([(int) $campus->id], array_column($data['campus_scopes'], 'campus_id'));
    }

    public function testOperatorContextRejectsTeacher(): void
    {
        try {
            $this->service()->operatorContext($this->context(1, 1, EducationRoleCode::Teacher), []);
            self::fail('Expected teacher role to be rejected by operator context.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::FORBIDDEN, $exception->getResponse()->code);
            self::assertSame('teacher', $exception->getResponse()->data['role_code']);
        }
    }

    private function service(): MobileContextService
    {
        return make(MobileContextService::class);
    }

    private function context(int $userId, int $tenantId, EducationRoleCode $roleCode): EducationUserContext
    {
        return new EducationUserContext(
            userId: $userId,
            tenantId: $tenantId,
            roleCode: $roleCode,
            platformAccess: false,
            campusIds: [],
            currentCampusId: null
        );
    }

    /**
     * @return array{0:EducationTenant, 1:User, 2:EducationUserProfile}
     */
    private function tenantUserProfile(string $roleCode): array
    {
        $user = User::query()->create(['username' => 'edum_svc_' . $roleCode]);
        $tenant = EducationTenant::query()->create([
            'name' => 'Mobile Service Tenant',
            'code' => 'mobile_service_' . $roleCode,
            'status' => 'enabled',
        ]);
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role_code' => $roleCode,
            'display_name' => 'Mobile User',
            'status' => 'enabled',
        ]);

        return [$tenant, $user, $profile];
    }
}
