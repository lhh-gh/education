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

use App\Model\Education\Foundation\EducationAuditLog;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationAuditLog>
 */
final class AuditLogRepository extends IRepository
{
    public function __construct(
        protected readonly EducationAuditLog $model
    ) {}

    public function createLog(array $data): EducationAuditLog
    {
        /* @var EducationAuditLog $log */
        return $this->create($data);
    }

    public function pageByContext(array $filters, EducationUserContext $context): array
    {
        $page = (int) ($filters['page'] ?? 1);
        $pageSize = (int) ($filters['pageSize'] ?? 20);
        unset($filters['page'], $filters['pageSize']);

        $query = $this->getQuery();
        $this->applyContextScope($query, $context);
        $this->applyFilters($query, $filters, $context);

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(
                perPage: $pageSize,
                pageName: self::PER_PAGE_PARAM_NAME,
                page: $page
            );

        return [
            'list' => $paginator->items(),
            'total' => $paginator->total(),
        ];
    }

    public function findVisibleById(int $id, EducationUserContext $context): ?EducationAuditLog
    {
        $query = $this->getQuery()->whereKey($id);
        $this->applyContextScope($query, $context);
        $log = $query->first();

        return $log instanceof EducationAuditLog ? $log : null;
    }

    private function applyContextScope(Builder $query, EducationUserContext $context): void
    {
        if ($context->platformAccess) {
            return;
        }

        $query->where('tenant_id', $context->tenantId);

        if (! $context->roleCode->requiresCampusScope()) {
            return;
        }

        $query->where(static function (Builder $query) use ($context): void {
            $query->whereNull('campus_id');
            if ($context->campusIds !== []) {
                $query->orWhereIn('campus_id', $context->campusIds);
            }
        });
    }

    private function applyFilters(Builder $query, array $filters, EducationUserContext $context): void
    {
        $query
            ->when($context->platformAccess && isset($filters['tenant_id']) && $filters['tenant_id'] !== '', static function (Builder $query) use ($filters): void {
                $query->where('tenant_id', (int) $filters['tenant_id']);
            })
            ->when(isset($filters['campus_id']) && $filters['campus_id'] !== '' && $this->canFilterCampus((int) $filters['campus_id'], $context), static function (Builder $query) use ($filters): void {
                $query->where('campus_id', (int) $filters['campus_id']);
            })
            ->when(isset($filters['module']) && $filters['module'] !== '', static function (Builder $query) use ($filters): void {
                $query->where('module', $filters['module']);
            })
            ->when(isset($filters['resource']) && $filters['resource'] !== '', static function (Builder $query) use ($filters): void {
                $query->where('resource', $filters['resource']);
            })
            ->when(isset($filters['action']) && $filters['action'] !== '', static function (Builder $query) use ($filters): void {
                $query->where('action', $filters['action']);
            })
            ->when(isset($filters['business_type']) && $filters['business_type'] !== '', static function (Builder $query) use ($filters): void {
                $query->where('business_type', $filters['business_type']);
            })
            ->when(isset($filters['business_id']) && $filters['business_id'] !== '', static function (Builder $query) use ($filters): void {
                $query->where('business_id', (string) $filters['business_id']);
            })
            ->when(isset($filters['actor_user_id']) && $filters['actor_user_id'] !== '', static function (Builder $query) use ($filters): void {
                $query->where('actor_user_id', (int) $filters['actor_user_id']);
            })
            ->when(isset($filters['actor_type']) && $filters['actor_type'] !== '', static function (Builder $query) use ($filters): void {
                $query->where('actor_type', $filters['actor_type']);
            })
            ->when(isset($filters['start_at']) && $filters['start_at'] !== '', static function (Builder $query) use ($filters): void {
                $query->where('created_at', '>=', $filters['start_at']);
            })
            ->when(isset($filters['end_at']) && $filters['end_at'] !== '', static function (Builder $query) use ($filters): void {
                $query->where('created_at', '<=', $filters['end_at']);
            })
            ->when(isset($filters['keyword']) && $filters['keyword'] !== '', static function (Builder $query) use ($filters): void {
                $keyword = '%' . $filters['keyword'] . '%';
                $query->where(static function (Builder $query) use ($keyword): void {
                    $query->where('action', 'like', $keyword)
                        ->orWhere('summary', 'like', $keyword)
                        ->orWhere('business_type', 'like', $keyword)
                        ->orWhere('business_id', 'like', $keyword)
                        ->orWhere('request_id', 'like', $keyword);
                });
            });
    }

    private function canFilterCampus(int $campusId, EducationUserContext $context): bool
    {
        return $context->platformAccess
            || ! $context->roleCode->requiresCampusScope()
            || $context->canAccessCampus($campusId);
    }
}
