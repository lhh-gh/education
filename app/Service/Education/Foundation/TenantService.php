<?php

declare(strict_types=1);

namespace App\Service\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Foundation\TenantStatus;
use App\Repository\Education\Foundation\TenantRepository;
use Carbon\Carbon;

final class TenantService
{
    public function __construct(
        private readonly TenantRepository $repository
    ) {
    }

    public function page(array $params, int $page, int $pageSize): array
    {
        return $this->repository->page($params, $page, $pageSize);
    }

    public function createTenant(array $data): EducationTenant
    {
        $this->assertUniqueCode((string) $data['code']);
        $data = $this->normalizeStatusTimestamps($data);

        return $this->repository->create($data);
    }

    public function updateTenant(int $id, array $data): EducationTenant
    {
        $tenant = $this->findTenantOrFail($id);
        $this->assertUniqueCode((string) $data['code'], $id);
        $data = $this->normalizeStatusTimestamps($data);
        $tenant->fill($data);
        $tenant->save();

        return $tenant->refresh();
    }

    public function changeStatus(int $id, string $status, ?int $operatorId): EducationTenant
    {
        $tenant = $this->findTenantOrFail($id);
        $data = $this->normalizeStatusTimestamps([
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ]);
        $tenant->fill($data);
        $tenant->save();

        return $tenant->refresh();
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
        return TenantStatus::tryFrom($status)?->value
            ?? throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid tenant status');
    }
}
