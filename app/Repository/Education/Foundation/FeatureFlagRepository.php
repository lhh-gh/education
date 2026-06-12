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

use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Repository\IRepository;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationFeatureFlag>
 */
final class FeatureFlagRepository extends IRepository
{
    public function __construct(
        protected readonly EducationFeatureFlag $model
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
            ->when(isset($params['feature_code']) && $params['feature_code'] !== '', static function (Builder $query) use ($params): void {
                $query->where('feature_code', $params['feature_code']);
            })
            ->when(\array_key_exists('enabled', $params) && $params['enabled'] !== '', static function (Builder $query) use ($params): void {
                $query->where('enabled', (bool) $params['enabled']);
            })
            ->when(isset($params['status']) && $params['status'] !== '', static function (Builder $query) use ($params): void {
                $query->where('status', $params['status']);
            })
            ->when(isset($params['keyword']) && $params['keyword'] !== '', static function (Builder $query) use ($params): void {
                $query->where(static function (Builder $query) use ($params): void {
                    $keyword = '%' . $params['keyword'] . '%';
                    $query->where('feature_code', 'like', $keyword)
                        ->orWhere('feature_name', 'like', $keyword);
                });
            })
            ->orderBy('feature_code')
            ->orderBy('id');

        return $query;
    }

    public function findActiveByOwnerFeature(string $ownerKey, string $featureCode, string $now): ?EducationFeatureFlag
    {
        $flag = $this->getQuery()
            ->where('owner_key', $ownerKey)
            ->where('feature_code', $featureCode)
            ->where('status', 'enabled')
            ->where(static function (Builder $query) use ($now): void {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', $now);
            })
            ->where(static function (Builder $query) use ($now): void {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $now);
            })
            ->first();

        return $flag instanceof EducationFeatureFlag ? $flag : null;
    }

    public function existsByOwnerFeature(string $ownerKey, string $featureCode, ?int $ignoreId = null): bool
    {
        return $this->getQuery()
            ->where('owner_key', $ownerKey)
            ->where('feature_code', $featureCode)
            ->when($ignoreId !== null, static fn (Builder $query) => $query->where('id', '<>', $ignoreId))
            ->exists();
    }
}
