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
use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Model\Enums\Education\Foundation\FeatureFlagStatus;
use App\Repository\Education\Foundation\FeatureFlagRepository;
use App\Repository\Education\Foundation\TenantRepository;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class FeatureFlagService
{
    public function __construct(
        private readonly FeatureFlagRepository $repository,
        private readonly TenantRepository $tenantRepository,
        private readonly ConfigOwnerResolver $ownerResolver,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $params, int $page, int $pageSize, EducationUserContext $context): array
    {
        if (! $context->platformAccess) {
            if (isset($params['tenant_id']) && (int) $params['tenant_id'] !== (int) $context->tenantId) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => (int) $params['tenant_id']]);
            }
            $ownerKeys = [$this->ownerResolver->systemOwnerKey()];
            if ($context->tenantId !== null) {
                $ownerKeys[] = $this->ownerResolver->tenantOwnerKey($context->tenantId);
            }
            $params['owner_keys'] = $ownerKeys;
        }

        return $this->repository->page($params, $page, $pageSize);
    }

    public function createFlag(array $data, EducationUserContext $context, ?int $operatorId): EducationFeatureFlag
    {
        $tenantId = $this->normalizeTenantId($data['tenant_id'] ?? null);
        $ownerType = (string) $data['owner_type'];
        $this->ownerResolver->assertCanWriteOwner($context, $ownerType, $tenantId);
        $this->assertTenantExists($ownerType, $tenantId);
        $this->assertEffectiveWindow($data['effective_from'] ?? null, $data['effective_to'] ?? null);
        $ownerKey = $this->ownerResolver->resolveOwnerKey($ownerType, $tenantId);
        $featureCode = (string) $data['feature_code'];
        $this->assertUniqueFeature($ownerKey, $featureCode);

        $payload = [
            'owner_type' => $ownerType,
            'tenant_id' => $tenantId,
            'owner_key' => $ownerKey,
            'feature_code' => $featureCode,
            'feature_name' => $data['feature_name'],
            'description' => $data['description'] ?? null,
            'enabled' => (bool) $data['enabled'],
            'config' => $data['config'] ?? [],
            'effective_from' => $data['effective_from'] ?? null,
            'effective_to' => $data['effective_to'] ?? null,
            'status' => $this->normalizeStatus((string) ($data['status'] ?? FeatureFlagStatus::Enabled->value)),
            'is_locked' => (bool) ($data['is_locked'] ?? false),
            'created_by' => $operatorId,
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($payload, $context): EducationFeatureFlag {
            $flag = $this->repository->create($payload);
            $flag = $flag->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.feature_flag.created',
                businessId: (int) $flag->id,
                context: $context,
                before: [],
                after: $flag->toArray(),
                metadata: $this->flagMetadata($flag),
                summary: sprintf('Feature flag %s created', $flag->feature_code)
            );

            return $flag;
        });
    }

    public function updateFlag(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationFeatureFlag
    {
        $flag = $this->findFlagOrFail($id);
        $this->assertFlagWritable($flag, $context);
        $this->assertEffectiveWindow($data['effective_from'] ?? $flag->effective_from, $data['effective_to'] ?? $flag->effective_to);
        $data = [
            'feature_name' => $data['feature_name'] ?? $flag->feature_name,
            'description' => $data['description'] ?? $flag->description,
            'enabled' => (bool) ($data['enabled'] ?? $flag->enabled),
            'config' => $data['config'] ?? $flag->config,
            'effective_from' => $data['effective_from'] ?? $flag->effective_from,
            'effective_to' => $data['effective_to'] ?? $flag->effective_to,
            'status' => $this->normalizeStatus((string) ($data['status'] ?? $flag->status)),
            'is_locked' => (bool) ($data['is_locked'] ?? $flag->is_locked),
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($flag, $data, $context): EducationFeatureFlag {
            $before = $flag->toArray();
            $flag->fill($data);
            $flag->save();
            $flag = $flag->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.feature_flag.updated',
                businessId: (int) $flag->id,
                context: $context,
                before: $before,
                after: $flag->toArray(),
                metadata: $this->flagMetadata($flag),
                summary: sprintf('Feature flag %s updated', $flag->feature_code)
            );

            return $flag;
        });
    }

    public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationFeatureFlag
    {
        $flag = $this->findFlagOrFail($id);
        $this->assertFlagWritable($flag, $context);
        $data = [
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($flag, $data, $context): EducationFeatureFlag {
            $before = $flag->toArray();
            $flag->fill($data);
            $flag->save();
            $flag = $flag->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.feature_flag.status_changed',
                businessId: (int) $flag->id,
                context: $context,
                before: $before,
                after: $flag->toArray(),
                metadata: $this->flagMetadata($flag),
                summary: sprintf('Feature flag %s status changed', $flag->feature_code)
            );

            return $flag;
        });
    }

    public function deleteFlag(int $id, EducationUserContext $context): void
    {
        $flag = $this->findFlagOrFail($id);
        $this->assertFlagWritable($flag, $context);
        if ($flag->is_locked) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'locked system flag cannot be deleted');
        }

        $flag->delete();
    }

    public function enabled(string $featureCode, ?int $tenantId, ?string $now = null): bool
    {
        $resolved = $this->activeFlag($featureCode, $tenantId, $now ?? Carbon::now()->toDateTimeString());

        return $resolved instanceof EducationFeatureFlag && (bool) $resolved->enabled;
    }

    public function resolved(string $featureCode, ?int $tenantId, ?string $now = null): array
    {
        $flag = $this->activeFlag($featureCode, $tenantId, $now ?? Carbon::now()->toDateTimeString());
        if (! $flag instanceof EducationFeatureFlag) {
            return [
                'feature_code' => $featureCode,
                'enabled' => false,
                'owner_key' => null,
                'config' => [],
                'effective_from' => null,
                'effective_to' => null,
            ];
        }

        return [
            'feature_code' => $flag->feature_code,
            'enabled' => (bool) $flag->enabled,
            'owner_key' => $flag->owner_key,
            'config' => $flag->config ?? [],
            'effective_from' => $flag->effective_from?->toDateTimeString(),
            'effective_to' => $flag->effective_to?->toDateTimeString(),
        ];
    }

    private function activeFlag(string $featureCode, ?int $tenantId, string $now): ?EducationFeatureFlag
    {
        if ($tenantId !== null) {
            $tenantFlag = $this->repository->findActiveByOwnerFeature($this->ownerResolver->tenantOwnerKey($tenantId), $featureCode, $now);
            if ($tenantFlag instanceof EducationFeatureFlag) {
                return $tenantFlag;
            }
        }

        return $this->repository->findActiveByOwnerFeature($this->ownerResolver->systemOwnerKey(), $featureCode, $now);
    }

    private function findFlagOrFail(int $id): EducationFeatureFlag
    {
        $flag = $this->repository->findById($id);
        if (! $flag instanceof EducationFeatureFlag) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        return $flag;
    }

    private function assertFlagWritable(EducationFeatureFlag $flag, EducationUserContext $context): void
    {
        $this->ownerResolver->assertCanWriteOwner($context, $flag->owner_type, $flag->tenant_id);
    }

    private function assertTenantExists(string $ownerType, ?int $tenantId): void
    {
        if ($ownerType !== 'tenant') {
            return;
        }
        if ($tenantId === null || ! $this->tenantRepository->existsById($tenantId)) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'education tenant not found', ['tenant_id' => $tenantId]);
        }
    }

    private function assertUniqueFeature(string $ownerKey, string $featureCode): void
    {
        if ($this->repository->existsByOwnerFeature($ownerKey, $featureCode)) {
            throw new BusinessException(ResultCode::CONFLICT, 'feature flag already exists', ['owner_key' => $ownerKey, 'feature_code' => $featureCode]);
        }
    }

    private function assertEffectiveWindow(mixed $effectiveFrom, mixed $effectiveTo): void
    {
        if ($effectiveFrom === null || $effectiveTo === null || $effectiveFrom === '' || $effectiveTo === '') {
            return;
        }

        if (Carbon::parse((string) $effectiveTo)->lessThanOrEqualTo(Carbon::parse((string) $effectiveFrom))) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'effective_to must be greater than effective_from');
        }
    }

    private function normalizeStatus(string $status): string
    {
        if (FeatureFlagStatus::tryFrom($status) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid feature flag status');
        }

        return FeatureFlagStatus::from($status)->value;
    }

    private function normalizeTenantId(mixed $tenantId): ?int
    {
        if ($tenantId === null || $tenantId === '') {
            return null;
        }

        return (int) $tenantId;
    }

    private function flagMetadata(EducationFeatureFlag $flag): array
    {
        return array_filter([
            'tenant_id' => $flag->tenant_id === null ? null : (int) $flag->tenant_id,
            'owner_key' => $flag->owner_key,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function dispatchAudit(
        string $action,
        int $businessId,
        EducationUserContext $context,
        array $before,
        array $after,
        array $metadata,
        string $summary
    ): void {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'foundation',
            resource: 'feature_flag',
            action: $action,
            businessType: 'feature_flag',
            businessId: $businessId,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: $metadata,
            summary: $summary
        ));
    }
}
