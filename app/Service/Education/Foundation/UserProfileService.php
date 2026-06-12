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
use App\Event\Education\Foundation\EducationAuditEvent;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Enums\Education\Foundation\UserProfileStatus;
use App\Model\Permission\User;
use App\Repository\Education\Foundation\UserCampusScopeRepository;
use App\Repository\Education\Foundation\UserProfileRepository;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class UserProfileService
{
    public function __construct(
        private readonly UserProfileRepository $repository,
        private readonly UserCampusScopeRepository $scopeRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $params, int $page, int $pageSize, EducationUserContext $context): array
    {
        if (! $context->platformAccess) {
            $params['tenant_id'] = $context->tenantId;
        }

        return $this->repository->page($params, $page, $pageSize);
    }

    public function createProfile(array $data, ?int $operatorId, ?EducationUserContext $context = null): EducationUserProfile
    {
        $roleCode = $this->normalizeRoleCode((string) $data['role_code']);
        $tenantId = $this->normalizeTenantId($data['tenant_id'] ?? null);
        $userId = (int) $data['user_id'];
        $this->assertUserExists($userId);
        $this->assertTenantRule($roleCode, $tenantId);
        $this->assertTenantAndCampus($tenantId, $data['current_campus_id'] ?? null);
        $profileKey = $this->buildProfileKey($tenantId, $userId);
        $this->assertUniqueProfileKey($profileKey);

        $data['tenant_id'] = $tenantId;
        $data['user_id'] = $userId;
        $data['profile_key'] = $profileKey;
        $data['role_code'] = $roleCode->value;
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? UserProfileStatus::Enabled->value));
        $data['created_by'] = $operatorId;
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($data, $context): EducationUserProfile {
            $profile = $this->repository->create($data);
            $profile = $profile->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.user_profile.created',
                businessId: (int) $profile->id,
                context: $context,
                before: [],
                after: $profile->toArray(),
                metadata: $this->profileMetadata($profile),
                summary: sprintf('User profile %s created', $profile->profile_key)
            );

            return $profile;
        });
    }

    public function updateProfile(int $id, array $data, ?int $operatorId, ?EducationUserContext $context = null): EducationUserProfile
    {
        $profile = $this->findProfileOrFail($id);
        $roleCode = $this->normalizeRoleCode((string) $data['role_code']);
        $tenantId = $this->normalizeTenantId($data['tenant_id'] ?? null);
        $userId = (int) $data['user_id'];
        $this->assertUserExists($userId);
        $this->assertTenantRule($roleCode, $tenantId);
        $this->assertTenantAndCampus($tenantId, $data['current_campus_id'] ?? null);
        $profileKey = $this->buildProfileKey($tenantId, $userId);
        $this->assertUniqueProfileKey($profileKey, $id);

        $data['tenant_id'] = $tenantId;
        $data['user_id'] = $userId;
        $data['profile_key'] = $profileKey;
        $data['role_code'] = $roleCode->value;
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? $profile->status->value));
        $data['updated_by'] = $operatorId;

        return Db::transaction(function () use ($profile, $data, $context): EducationUserProfile {
            $before = $profile->toArray();
            $profile->fill($data);
            $profile->save();
            $profile = $profile->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.user_profile.updated',
                businessId: (int) $profile->id,
                context: $context,
                before: $before,
                after: $profile->toArray(),
                metadata: $this->profileMetadata($profile),
                summary: sprintf('User profile %s updated', $profile->profile_key)
            );

            return $profile;
        });
    }

    public function changeStatus(int $id, string $status, ?int $operatorId, ?EducationUserContext $context = null): EducationUserProfile
    {
        $profile = $this->findProfileOrFail($id);
        $data = [
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($profile, $data, $context): EducationUserProfile {
            $before = $profile->toArray();
            $profile->fill($data);
            $profile->save();
            $profile = $profile->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.user_profile.status_changed',
                businessId: (int) $profile->id,
                context: $context,
                before: $before,
                after: $profile->toArray(),
                metadata: $this->profileMetadata($profile),
                summary: sprintf('User profile %s status changed', $profile->profile_key)
            );

            return $profile;
        });
    }

    public function resolveForUser(int $userId, ?int $requestedTenantId): EducationUserContext
    {
        $profiles = $this->repository->getQuery()
            ->where('user_id', $userId)
            ->get();
        if ($profiles->isEmpty()) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education user profile is missing', ['user_id' => $userId]);
        }

        $profile = null;
        if ($requestedTenantId !== null) {
            $profile = $profiles->first(static function (Model $profile) use ($requestedTenantId): bool {
                return $profile instanceof EducationUserProfile
                    && ($profile->tenant_id === null || (int) $profile->tenant_id === $requestedTenantId);
            });
        } else {
            $profile = $profiles->first(static fn (Model $profile): bool => $profile instanceof EducationUserProfile && $profile->tenant_id !== null)
                ?? $profiles->first();
        }

        if (! $profile instanceof EducationUserProfile) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => $requestedTenantId]);
        }

        if ($profile->status !== UserProfileStatus::Enabled) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education user profile is disabled', ['user_id' => $userId]);
        }

        $roleCode = $profile->role_code;
        $tenantId = $profile->tenant_id === null ? $requestedTenantId : (int) $profile->tenant_id;
        $campusIds = $tenantId === null ? [] : $this->scopeRepository->campusIdsForProfile($tenantId, (int) $profile->id);

        return new EducationUserContext(
            userId: $userId,
            tenantId: $tenantId,
            roleCode: $roleCode,
            platformAccess: $roleCode->isPlatform(),
            campusIds: $campusIds,
            currentCampusId: $profile->current_campus_id
        );
    }

    public function buildProfileKey(?int $tenantId, int $userId): string
    {
        return $tenantId === null ? 'platform:' . $userId : 'tenant:' . $tenantId . ':' . $userId;
    }

    public function findProfileOrFail(int $id): EducationUserProfile
    {
        $profile = $this->repository->findById($id);
        if (! $profile instanceof EducationUserProfile) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        return $profile;
    }

    private function normalizeRoleCode(string $roleCode): EducationRoleCode
    {
        $role = EducationRoleCode::tryFrom($roleCode);
        if ($role === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid education role code');
        }

        return $role;
    }

    private function normalizeStatus(string $status): string
    {
        if (UserProfileStatus::tryFrom($status) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid education user profile status');
        }

        return UserProfileStatus::from($status)->value;
    }

    private function normalizeTenantId(mixed $tenantId): ?int
    {
        if ($tenantId === null || $tenantId === '') {
            return null;
        }

        return (int) $tenantId;
    }

    private function assertTenantRule(EducationRoleCode $roleCode, ?int $tenantId): void
    {
        if ($roleCode->isPlatform() && $tenantId !== null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'platform education role requires empty tenant_id');
        }

        if (! $roleCode->isPlatform() && $tenantId === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'tenant education role requires tenant_id');
        }
    }

    private function assertTenantAndCampus(?int $tenantId, mixed $currentCampusId): void
    {
        if ($tenantId !== null && ! EducationTenant::query()->whereKey($tenantId)->exists()) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'education tenant not found', ['tenant_id' => $tenantId]);
        }

        if ($currentCampusId === null || $currentCampusId === '') {
            return;
        }

        if ($tenantId === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'platform education profile cannot have current_campus_id');
        }

        $exists = EducationCampus::query()
            ->where('tenant_id', $tenantId)
            ->whereKey((int) $currentCampusId)
            ->exists();
        if (! $exists) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current tenant', ['campus_id' => (int) $currentCampusId]);
        }
    }

    private function assertUserExists(int $userId): void
    {
        if (! User::query()->whereKey($userId)->exists()) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'MineAdmin user not found', ['user_id' => $userId]);
        }
    }

    private function assertUniqueProfileKey(string $profileKey, ?int $ignoreId = null): void
    {
        if ($this->repository->existsByProfileKey($profileKey, $ignoreId)) {
            throw new BusinessException(
                ResultCode::CONFLICT,
                'education user profile already exists',
                ['profile_key' => $profileKey]
            );
        }
    }

    private function profileMetadata(EducationUserProfile $profile): array
    {
        return array_filter([
            'tenant_id' => $profile->tenant_id === null ? null : (int) $profile->tenant_id,
            'campus_id' => $profile->current_campus_id === null ? null : (int) $profile->current_campus_id,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function dispatchAudit(
        string $action,
        int $businessId,
        ?EducationUserContext $context,
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
            resource: 'user_profile',
            action: $action,
            businessType: 'user_profile',
            businessId: $businessId,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: $metadata,
            summary: $summary
        ));
    }
}
