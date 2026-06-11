<?php

declare(strict_types=1);

namespace App\Repository\Education\Foundation;

use App\Model\Education\Foundation\EducationCampus;
use App\Repository\IRepository;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationCampus>
 */
final class CampusRepository extends IRepository
{
    public function __construct(
        protected readonly EducationCampus $model
    ) {
    }

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->where('tenant_id', (int) $params['tenant_id'])
            ->when(isset($params['keyword']) && $params['keyword'] !== '', static function (Builder $query) use ($params): void {
                $query->where(static function (Builder $query) use ($params): void {
                    $keyword = '%' . $params['keyword'] . '%';
                    $query->where('name', 'like', $keyword)
                        ->orWhere('code', 'like', $keyword)
                        ->orWhere('contact_phone', 'like', $keyword)
                        ->orWhere('address', 'like', $keyword);
                });
            })
            ->when(isset($params['status']) && $params['status'] !== '', static function (Builder $query) use ($params): void {
                $query->where('status', $params['status']);
            })
            ->orderByDesc('id');
    }

    public function existsByTenantCode(int $tenantId, string $code, ?int $ignoreId = null): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->when($ignoreId !== null, static fn (Builder $query) => $query->where('id', '<>', $ignoreId))
            ->exists();
    }

    public function findInTenant(int $tenantId, int $id): ?EducationCampus
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->whereKey($id)
            ->first();
    }
}
