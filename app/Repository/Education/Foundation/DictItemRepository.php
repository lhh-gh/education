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

use App\Model\Education\Foundation\EducationDictItem;
use App\Repository\IRepository;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationDictItem>
 */
final class DictItemRepository extends IRepository
{
    public function __construct(
        protected readonly EducationDictItem $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        $query
            ->when(isset($params['dict_type_id']) && $params['dict_type_id'] !== '', static function (Builder $query) use ($params): void {
                $query->where('dict_type_id', (int) $params['dict_type_id']);
            })
            ->when(isset($params['owner_key']) && $params['owner_key'] !== '', static function (Builder $query) use ($params): void {
                $query->where('owner_key', $params['owner_key']);
            })
            ->when(isset($params['owner_keys']) && \is_array($params['owner_keys']), static function (Builder $query) use ($params): void {
                $query->whereIn('owner_key', $params['owner_keys']);
            })
            ->when(isset($params['dict_code']) && $params['dict_code'] !== '', static function (Builder $query) use ($params): void {
                $query->where('dict_code', $params['dict_code']);
            })
            ->when(isset($params['status']) && $params['status'] !== '', static function (Builder $query) use ($params): void {
                $query->where('status', $params['status']);
            })
            ->when(isset($params['keyword']) && $params['keyword'] !== '', static function (Builder $query) use ($params): void {
                $query->where(static function (Builder $query) use ($params): void {
                    $keyword = '%' . $params['keyword'] . '%';
                    $query->where('label', 'like', $keyword)
                        ->orWhere('value', 'like', $keyword);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id');

        return $query;
    }

    /**
     * @return Collection<int, EducationDictItem>
     */
    public function enabledItems(string $ownerKey, string $dictCode): Collection
    {
        return $this->getQuery()
            ->where('owner_key', $ownerKey)
            ->where('dict_code', $dictCode)
            ->where('status', 'enabled')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function existsValue(int $dictTypeId, string $value, ?int $ignoreId = null): bool
    {
        return $this->getQuery()
            ->where('dict_type_id', $dictTypeId)
            ->where('value', $value)
            ->when($ignoreId !== null, static fn (Builder $query) => $query->where('id', '<>', $ignoreId))
            ->exists();
    }
}
