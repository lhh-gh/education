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

use App\Model\Education\Foundation\EducationTenant;
use App\Repository\IRepository;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationTenant>
 */
final class TenantRepository extends IRepository
{
    public function __construct(
        protected readonly EducationTenant $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        $query
            ->when(isset($params['keyword']) && $params['keyword'] !== '', static function (Builder $query) use ($params): void {
                $query->where(static function (Builder $query) use ($params): void {
                    $keyword = '%' . $params['keyword'] . '%';
                    $query->where('name', 'like', $keyword)
                        ->orWhere('short_name', 'like', $keyword)
                        ->orWhere('code', 'like', $keyword)
                        ->orWhere('contact_phone', 'like', $keyword);
                });
            })
            ->when(isset($params['status']) && $params['status'] !== '', static function (Builder $query) use ($params): void {
                $query->where('status', $params['status']);
            })
            ->orderByDesc('id');

        return $query;
    }

    public function existsByCode(string $code, ?int $ignoreId = null): bool
    {
        return $this->getQuery()
            ->where('code', $code)
            ->when($ignoreId !== null, static fn (Builder $query) => $query->where('id', '<>', $ignoreId))
            ->exists();
    }
}
