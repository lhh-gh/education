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

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Enums\Education\Foundation\UserProfileStatus;
use App\Repository\Education\Foundation\UserCampusScopeRepository;
use App\Repository\Education\Foundation\UserProfileRepository;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class CampusScopeService
{
    public function __construct(
        private readonly UserProfileRepository $profileRepository,
        private readonly UserCampusScopeRepository $scopeRepository,
        private readonly EventDispatcherInterface $eventDispatcher
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
    public function saveScopes(int $profileId, int $tenantId, array $campusIds, ?int $operatorId, ?EducationUserContext $context = null): array
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

        Db::transaction(function () use ($tenantId, $profileId, $profile, $campusIds, $operatorId, $context): void {
            $beforeCampusIds = $this->scopeRepository->campusIdsForProfile($tenantId, $profileId);
            $this->scopeRepository->replaceScopes(
                tenantId: $tenantId,
                profileId: $profileId,
                userId: (int) $profile->user_id,
                campusIds: $campusIds,
                operatorId: $operatorId
            );
            $this->dispatchAudit(
                context: $context,
                businessId: $profileId,
                before: ['campus_ids' => $beforeCampusIds],
                after: ['campus_ids' => $campusIds],
                metadata: ['tenant_id' => $tenantId, 'user_profile_id' => $profileId],
                summary: \sprintf('Campus scopes for profile %s saved', $profile->profile_key)
            );
        });

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

    private function dispatchAudit(
        ?EducationUserContext $context,
        int $businessId,
        array $before,
        array $after,
        array $metadata,
        string $summary
    ): void {
        if (! $context instanceof EducationUserContext) {
            return;
        }

        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'foundation',
            resource: 'campus_scope',
            action: 'education.foundation.campus_scope.saved',
            businessType: 'campus_scope',
            businessId: $businessId,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: $metadata,
            summary: $summary
        ));
    }
}
