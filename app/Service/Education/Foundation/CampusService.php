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
use App\Model\Enums\Education\Foundation\CampusStatus;
use App\Repository\Education\Foundation\CampusRepository;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class CampusService
{
    public function __construct(
        private readonly CampusRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $params, int $page, int $pageSize): array
    {
        return $this->repository->page($params, $page, $pageSize);
    }

    public function createCampus(int $tenantId, array $data, ?EducationUserContext $context = null): EducationCampus
    {
        $this->assertTenantExists($tenantId);
        $this->assertUniqueCode($tenantId, (string) $data['code']);
        unset($data['tenant_id']);
        $data['tenant_id'] = $tenantId;
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? CampusStatus::Enabled->value));

        return Db::transaction(function () use ($data, $context): EducationCampus {
            $campus = $this->repository->create($data);
            $campus = $campus->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.campus.created',
                businessId: (int) $campus->id,
                context: $context,
                before: [],
                after: $campus->toArray(),
                metadata: ['tenant_id' => (int) $campus->tenant_id, 'campus_id' => (int) $campus->id],
                summary: sprintf('Campus %s created', $campus->name)
            );

            return $campus;
        });
    }

    public function updateCampus(int $tenantId, int $id, array $data, ?EducationUserContext $context = null): EducationCampus
    {
        $campus = $this->findCampusOrFail($tenantId, $id);
        $this->assertUniqueCode($tenantId, (string) $data['code'], $id);
        unset($data['tenant_id']);
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? $campus->status));

        return Db::transaction(function () use ($campus, $data, $context): EducationCampus {
            $before = $campus->toArray();
            $campus->fill($data);
            $campus->save();
            $campus = $campus->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.campus.updated',
                businessId: (int) $campus->id,
                context: $context,
                before: $before,
                after: $campus->toArray(),
                metadata: ['tenant_id' => (int) $campus->tenant_id, 'campus_id' => (int) $campus->id],
                summary: sprintf('Campus %s updated', $campus->name)
            );

            return $campus;
        });
    }

    public function changeStatus(int $tenantId, int $id, string $status, ?int $operatorId, ?EducationUserContext $context = null): EducationCampus
    {
        $campus = $this->findCampusOrFail($tenantId, $id);
        $data = [
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($campus, $data, $context): EducationCampus {
            $before = $campus->toArray();
            $campus->fill($data);
            $campus->save();
            $campus = $campus->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.campus.status_changed',
                businessId: (int) $campus->id,
                context: $context,
                before: $before,
                after: $campus->toArray(),
                metadata: ['tenant_id' => (int) $campus->tenant_id, 'campus_id' => (int) $campus->id],
                summary: sprintf('Campus %s status changed', $campus->name)
            );

            return $campus;
        });
    }

    public function deleteCampus(int $tenantId, int $id): void
    {
        $campus = $this->findCampusOrFail($tenantId, $id);
        $campus->delete();
    }

    private function assertTenantExists(int $tenantId): void
    {
        if (! EducationTenant::query()->whereKey($tenantId)->exists()) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }
    }

    private function findCampusOrFail(int $tenantId, int $id): EducationCampus
    {
        $campus = $this->repository->findInTenant($tenantId, $id);
        if (! $campus instanceof EducationCampus) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        return $campus;
    }

    private function assertUniqueCode(int $tenantId, string $code, ?int $ignoreId = null): void
    {
        if ($this->repository->existsByTenantCode($tenantId, $code, $ignoreId)) {
            throw new BusinessException(
                ResultCode::CONFLICT,
                'campus code already exists',
                ['code' => $code]
            );
        }
    }

    private function normalizeStatus(string $status): string
    {
        if (CampusStatus::tryFrom($status) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid campus status');
        }

        return CampusStatus::from($status)->value;
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
            resource: 'campus',
            action: $action,
            businessType: 'campus',
            businessId: $businessId,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: $metadata,
            summary: $summary
        ));
    }
}
