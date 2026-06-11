<?php

declare(strict_types=1);

namespace App\Service\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Foundation\CampusStatus;
use App\Repository\Education\Foundation\CampusRepository;

final class CampusService
{
    public function __construct(
        private readonly CampusRepository $repository
    ) {
    }

    public function page(array $params, int $page, int $pageSize): array
    {
        return $this->repository->page($params, $page, $pageSize);
    }

    public function createCampus(int $tenantId, array $data): EducationCampus
    {
        $this->assertTenantExists($tenantId);
        $this->assertUniqueCode($tenantId, (string) $data['code']);
        unset($data['tenant_id']);
        $data['tenant_id'] = $tenantId;
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? CampusStatus::Enabled->value));

        return $this->repository->create($data);
    }

    public function updateCampus(int $tenantId, int $id, array $data): EducationCampus
    {
        $campus = $this->findCampusOrFail($tenantId, $id);
        $this->assertUniqueCode($tenantId, (string) $data['code'], $id);
        unset($data['tenant_id']);
        $data['status'] = $this->normalizeStatus((string) ($data['status'] ?? $campus->status));
        $campus->fill($data);
        $campus->save();

        return $campus->refresh();
    }

    public function changeStatus(int $tenantId, int $id, string $status, ?int $operatorId): EducationCampus
    {
        $campus = $this->findCampusOrFail($tenantId, $id);
        $campus->fill([
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ]);
        $campus->save();

        return $campus->refresh();
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
        return CampusStatus::tryFrom($status)?->value
            ?? throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid campus status');
    }
}
