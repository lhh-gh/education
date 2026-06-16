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
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Enums\Education\Foundation\UserProfileStatus;
use App\Service\Education\Foundation\EducationUserContext;

final class GuardianMobileContextResolver
{
    public function resolveGuardian(EducationUserContext $context): EducationGuardian
    {
        $this->assertGuardianRole($context);

        if ($context->tenantId === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'guardian record is not bound');
        }

        $profile = EducationUserProfile::query()
            ->where('tenant_id', $context->tenantId)
            ->where('user_id', $context->userId)
            ->where('role_code', EducationRoleCode::Guardian->value)
            ->where('status', UserProfileStatus::Enabled->value)
            ->first();

        if (! $profile instanceof EducationUserProfile) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'guardian record is not bound', ['user_id' => $context->userId]);
        }

        $guardian = $this->findGuardianByProfile((int) $context->tenantId, $profile);
        if (! $guardian instanceof EducationGuardian) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'guardian record is not bound', ['profile_id' => (int) $profile->id]);
        }

        return $guardian;
    }

    public function assertGuardianRole(EducationUserContext $context): void
    {
        if ($context->roleCode !== EducationRoleCode::Guardian) {
            throw new BusinessException(
                ResultCode::FORBIDDEN,
                'guardian mobile role required',
                ['role_code' => $context->roleCode->value]
            );
        }
    }

    public function currentOperatorId(EducationUserContext $context): ?int
    {
        return $context->userId > 0 ? $context->userId : null;
    }

    private function findGuardianByProfile(int $tenantId, EducationUserProfile $profile): ?EducationGuardian
    {
        foreach (['unionid', 'openid', 'mobile'] as $field) {
            $value = (string) ($profile->{$field} ?? '');
            if ($value === '') {
                continue;
            }

            $guardian = EducationGuardian::query()
                ->where('tenant_id', $tenantId)
                ->where($field, $value)
                ->where('status', 'enabled')
                ->first();

            if ($guardian instanceof EducationGuardian) {
                return $guardian;
            }
        }

        return null;
    }
}
