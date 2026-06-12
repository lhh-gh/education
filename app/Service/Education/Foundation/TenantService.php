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
use App\Model\Enums\Education\Foundation\TenantStatus;
use App\Repository\Education\Foundation\TenantRepository;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class TenantService
{
    public function __construct(
        private readonly TenantRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $params, int $page, int $pageSize): array
    {
        return $this->repository->page($params, $page, $pageSize);
    }

    public function createTenant(array $data, ?EducationUserContext $context = null): EducationTenant
    {
        $this->assertUniqueCode((string) $data['code']);
        $data = $this->normalizeStatusTimestamps($data);

        return Db::transaction(function () use ($data, $context): EducationTenant {
            $tenant = $this->repository->create($data);
            $this->dispatchAudit(
                action: 'education.foundation.tenant.created',
                businessId: (int) $tenant->id,
                context: $context,
                before: [],
                after: $tenant->refresh()->toArray(),
                metadata: ['tenant_id' => (int) $tenant->id],
                summary: sprintf('Tenant %s created', $tenant->name)
            );

            return $tenant;
        });
    }

    public function updateTenant(int $id, array $data, ?EducationUserContext $context = null): EducationTenant
    {
        $tenant = $this->findTenantOrFail($id);
        $this->assertUniqueCode((string) $data['code'], $id);
        $data = $this->normalizeStatusTimestamps($data);

        return Db::transaction(function () use ($tenant, $data, $context): EducationTenant {
            $before = $tenant->toArray();
            $tenant->fill($data);
            $tenant->save();
            $tenant = $tenant->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.tenant.updated',
                businessId: (int) $tenant->id,
                context: $context,
                before: $before,
                after: $tenant->toArray(),
                metadata: ['tenant_id' => (int) $tenant->id],
                summary: sprintf('Tenant %s updated', $tenant->name)
            );

            return $tenant;
        });
    }

    public function changeStatus(int $id, string $status, ?int $operatorId, ?EducationUserContext $context = null): EducationTenant
    {
        $tenant = $this->findTenantOrFail($id);
        $data = $this->normalizeStatusTimestamps([
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ]);

        return Db::transaction(function () use ($tenant, $data, $context): EducationTenant {
            $before = $tenant->toArray();
            $tenant->fill($data);
            $tenant->save();
            $tenant = $tenant->refresh();
            $this->dispatchAudit(
                action: 'education.foundation.tenant.status_changed',
                businessId: (int) $tenant->id,
                context: $context,
                before: $before,
                after: $tenant->toArray(),
                metadata: ['tenant_id' => (int) $tenant->id],
                summary: sprintf('Tenant %s status changed', $tenant->name)
            );

            return $tenant;
        });
    }

    public function deleteTenant(int $id): void
    {
        $tenant = $this->findTenantOrFail($id);
        $hasCampuses = EducationCampus::query()
            ->where('tenant_id', $id)
            ->exists();
        if ($hasCampuses) {
            throw new BusinessException(ResultCode::FAIL, 'tenant has campuses');
        }

        $tenant->delete();
    }

    private function findTenantOrFail(int $id): EducationTenant
    {
        $tenant = $this->repository->findById($id);
        if (! $tenant instanceof EducationTenant) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        return $tenant;
    }

    private function assertUniqueCode(string $code, ?int $ignoreId = null): void
    {
        if ($this->repository->existsByCode($code, $ignoreId)) {
            throw new BusinessException(
                ResultCode::CONFLICT,
                'tenant code already exists',
                ['code' => $code]
            );
        }
    }

    private function normalizeStatusTimestamps(array $data): array
    {
        $status = $this->normalizeStatus((string) ($data['status'] ?? TenantStatus::Enabled->value));
        $data['status'] = $status;
        if ($status === TenantStatus::Enabled->value) {
            $data['enabled_at'] = Carbon::now();
        }
        if ($status === TenantStatus::Disabled->value) {
            $data['disabled_at'] = Carbon::now();
        }

        return $data;
    }

    private function normalizeStatus(string $status): string
    {
        if (TenantStatus::tryFrom($status) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid tenant status');
        }

        return TenantStatus::from($status)->value;
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
            resource: 'tenant',
            action: $action,
            businessType: 'tenant',
            businessId: $businessId,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: $metadata,
            summary: $summary
        ));
    }
}
