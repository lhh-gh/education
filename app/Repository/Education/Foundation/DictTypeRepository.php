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

namespace App\Repository\Education\Foundation;

use App\Model\Education\Foundation\EducationDictType;
use App\Repository\IRepository;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationDictType>
 */
final class DictTypeRepository extends IRepository
{
    public function __construct(
        protected readonly EducationDictType $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        $query
            ->when(isset($params['owner_type']) && $params['owner_type'] !== '', static function (Builder $query) use ($params): void {
                $query->where('owner_type', $params['owner_type']);
            })
            ->when(\array_key_exists('tenant_id', $params), static function (Builder $query) use ($params): void {
                $params['tenant_id'] === null
                    ? $query->whereNull('tenant_id')
                    : $query->where('tenant_id', (int) $params['tenant_id']);
            })
            ->when(isset($params['owner_key']) && $params['owner_key'] !== '', static function (Builder $query) use ($params): void {
                $query->where('owner_key', $params['owner_key']);
            })
            ->when(isset($params['owner_keys']) && \is_array($params['owner_keys']), static function (Builder $query) use ($params): void {
                $query->whereIn('owner_key', $params['owner_keys']);
            })
            ->when(isset($params['code']) && $params['code'] !== '', static function (Builder $query) use ($params): void {
                $query->where('code', $params['code']);
            })
            ->when(isset($params['status']) && $params['status'] !== '', static function (Builder $query) use ($params): void {
                $query->where('status', $params['status']);
            })
            ->when(isset($params['keyword']) && $params['keyword'] !== '', static function (Builder $query) use ($params): void {
                $query->where(static function (Builder $query) use ($params): void {
                    $keyword = '%' . $params['keyword'] . '%';
                    $query->where('code', 'like', $keyword)
                        ->orWhere('name', 'like', $keyword);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id');

        return $query;
    }

    public function findByOwnerCode(string $ownerKey, string $code): ?EducationDictType
    {
        $type = $this->getQuery()
            ->where('owner_key', $ownerKey)
            ->where('code', $code)
            ->first();

        return $type instanceof EducationDictType ? $type : null;
    }

    public function existsByOwnerCode(string $ownerKey, string $code, ?int $ignoreId = null): bool
    {
        return $this->getQuery()
            ->where('owner_key', $ownerKey)
            ->where('code', $code)
            ->when($ignoreId !== null, static fn (Builder $query) => $query->where('id', '<>', $ignoreId))
            ->exists();
    }
}
