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

use App\Model\Education\Foundation\EducationUserProfile;
use App\Repository\IRepository;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationUserProfile>
 */
final class UserProfileRepository extends IRepository
{
    public function __construct(
        protected readonly EducationUserProfile $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        $query
            ->when(\array_key_exists('tenant_id', $params), static function (Builder $query) use ($params): void {
                $params['tenant_id'] === null
                    ? $query->whereNull('tenant_id')
                    : $query->where('tenant_id', (int) $params['tenant_id']);
            })
            ->when(isset($params['user_id']) && $params['user_id'] !== '', static function (Builder $query) use ($params): void {
                $query->where('user_id', (int) $params['user_id']);
            })
            ->when(isset($params['role_code']) && $params['role_code'] !== '', static function (Builder $query) use ($params): void {
                $query->where('role_code', $params['role_code']);
            })
            ->when(isset($params['status']) && $params['status'] !== '', static function (Builder $query) use ($params): void {
                $query->where('status', $params['status']);
            })
            ->when(isset($params['keyword']) && $params['keyword'] !== '', static function (Builder $query) use ($params): void {
                $query->where(static function (Builder $query) use ($params): void {
                    $keyword = '%' . $params['keyword'] . '%';
                    $query->where('display_name', 'like', $keyword)
                        ->orWhere('mobile', 'like', $keyword)
                        ->orWhere('openid', 'like', $keyword)
                        ->orWhere('unionid', 'like', $keyword);
                });
            })
            ->orderByDesc('id');

        return $query;
    }

    public function findByProfileKey(string $profileKey): ?EducationUserProfile
    {
        $profile = $this->getQuery()->where('profile_key', $profileKey)->first();

        return $profile instanceof EducationUserProfile ? $profile : null;
    }

    public function findByUserTenant(int $userId, ?int $tenantId): ?EducationUserProfile
    {
        $profile = $this->getQuery()
            ->where('user_id', $userId)
            ->when($tenantId === null, static fn (Builder $query) => $query->whereNull('tenant_id'), static fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->first();

        return $profile instanceof EducationUserProfile ? $profile : null;
    }

    public function listEnabledByUser(int $userId): Collection
    {
        return $this->getQuery()
            ->where('user_id', $userId)
            ->where('status', 'enabled')
            ->get();
    }

    public function existsByProfileKey(string $profileKey, ?int $ignoreId = null): bool
    {
        return $this->getQuery()
            ->where('profile_key', $profileKey)
            ->when($ignoreId !== null, static fn (Builder $query) => $query->where('id', '<>', $ignoreId))
            ->exists();
    }
}
