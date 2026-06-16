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

namespace App\Service\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Enums\Education\Foundation\UserProfileStatus;
use App\Service\Education\Foundation\EducationUserContext;

final class TeacherMobileContextResolver
{
    public function resolveTeacher(EducationUserContext $context): EducationTeacher
    {
        $this->assertTeacherRole($context);

        if ($context->tenantId === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher profile is not enabled');
        }

        $profile = EducationUserProfile::query()
            ->where('tenant_id', $context->tenantId)
            ->where('user_id', $context->userId)
            ->where('role_code', EducationRoleCode::Teacher->value)
            ->where('status', UserProfileStatus::Enabled->value)
            ->first();

        if (! $profile instanceof EducationUserProfile) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher profile is not enabled', ['user_id' => $context->userId]);
        }

        $teacher = EducationTeacher::query()
            ->where('tenant_id', $context->tenantId)
            ->where('user_profile_id', (int) $profile->id)
            ->where('status', 'enabled')
            ->first();

        if (! $teacher instanceof EducationTeacher) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher profile is not enabled', ['user_profile_id' => (int) $profile->id]);
        }

        return $teacher;
    }

    public function assertTeacherRole(EducationUserContext $context): void
    {
        if ($context->roleCode !== EducationRoleCode::Teacher) {
            throw new BusinessException(
                ResultCode::FORBIDDEN,
                'teacher mobile role required',
                ['role_code' => $context->roleCode->value]
            );
        }
    }

    public function assertCampusAllowed(EducationUserContext $context, ?int $campusId): ?int
    {
        if ($campusId === null) {
            return $context->currentCampusId;
        }

        if (! $context->canAccessCampus($campusId)) {
            throw new BusinessException(
                ResultCode::FORBIDDEN,
                'campus is outside teacher scope',
                ['campus_id' => $campusId]
            );
        }

        return $campusId;
    }

    public function currentOperatorId(EducationUserContext $context): ?int
    {
        return $context->userId > 0 ? $context->userId : null;
    }
}
