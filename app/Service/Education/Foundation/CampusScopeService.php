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

namespace App\Service\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Enums\Education\Foundation\UserProfileStatus;
use App\Repository\Education\Foundation\UserCampusScopeRepository;
use App\Repository\Education\Foundation\UserProfileRepository;

final class CampusScopeService
{
    public function __construct(
        private readonly UserProfileRepository $profileRepository,
        private readonly UserCampusScopeRepository $scopeRepository
    ) {}

    /**
     * @return int[]
     */
    public function campusIdsForUser(int $tenantId, int $userId): array
    {
        return $this->scopeRepository->campusIdsForUser($tenantId, $userId);
    }

    /**
     * @return int[]
     */
    public function campusIdsForProfile(int $tenantId, int $profileId): array
    {
        return $this->scopeRepository->campusIdsForProfile($tenantId, $profileId);
    }

    /**
     * @param int[] $campusIds
     * @return int[]
     */
    public function saveScopes(int $profileId, int $tenantId, array $campusIds, ?int $operatorId): array
    {
        $profile = $this->profileRepository->findById($profileId);
        if (! $profile instanceof EducationUserProfile) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        if ((int) $profile->tenant_id !== $tenantId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => $tenantId]);
        }

        $campusIds = array_values(array_unique(array_map('intval', $campusIds)));
        sort($campusIds);
        $this->assertScopeRequired($profile, $campusIds);
        foreach ($campusIds as $campusId) {
            $this->assertCampusInTenant($tenantId, $campusId);
        }

        $this->scopeRepository->replaceScopes(
            tenantId: $tenantId,
            profileId: $profileId,
            userId: (int) $profile->user_id,
            campusIds: $campusIds,
            operatorId: $operatorId
        );

        return $campusIds;
    }

    public function assertCampusInScope(EducationUserContext $context, int $campusId): void
    {
        if ($context->platformAccess) {
            return;
        }

        if ($context->tenantId === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope');
        }

        $this->assertCampusInTenant($context->tenantId, $campusId);
        if ($context->roleCode === EducationRoleCode::TenantAdmin) {
            return;
        }

        if (! $context->canAccessCampus($campusId)) {
            throw new BusinessException(
                ResultCode::FORBIDDEN,
                'campus is outside current user scope',
                ['campus_id' => $campusId]
            );
        }
    }

    /**
     * @param int[] $campusIds
     */
    private function assertScopeRequired(EducationUserProfile $profile, array $campusIds): void
    {
        if ($profile->status !== UserProfileStatus::Enabled) {
            return;
        }

        if ($profile->role_code->requiresCampusScope() && $campusIds === []) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'enabled education role requires campus scope');
        }
    }

    private function assertCampusInTenant(int $tenantId, int $campusId): void
    {
        $exists = EducationCampus::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($campusId)
            ->exists();

        if (! $exists) {
            throw new BusinessException(
                ResultCode::FORBIDDEN,
                'campus is outside current tenant',
                ['campus_id' => $campusId]
            );
        }
    }
}
