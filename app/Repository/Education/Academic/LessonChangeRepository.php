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

namespace App\Repository\Education\Academic;

use App\Model\Education\Academic\EducationLessonChangeRecord;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationLessonChangeRecord>
 */
final class LessonChangeRepository extends IRepository
{
    public function __construct(
        protected readonly EducationLessonChangeRecord $model
    ) {}

    public function page(array $filters = [], mixed $page = null, ?int $pageSize = null): array
    {
        if (! $page instanceof EducationUserContext) {
            return parent::page($filters, \is_int($page) ? $page : null, $pageSize);
        }
        $context = $page;
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? 15)));
        unset($filters['page'], $filters['pageSize'], $filters['page_size']);

        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters);
        $query->orderByDesc('created_at')->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationLessonChangeRecord
    {
        $row = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $row instanceof EducationLessonChangeRecord ? $row : null;
    }

    public function createRecord(array $data): EducationLessonChangeRecord
    {
        return $this->create($data);
    }

    public function hasMakeupForLeave(int $leaveRequestId, int $tenantId): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('leave_request_id', $leaveRequestId)
            ->where('change_type', 'makeup')
            ->exists();
    }

    public function hasRescheduleForLessonAtTarget(int $sourceLessonId, string $targetStartAt, string $targetEndAt, int $tenantId): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('source_lesson_id', $sourceLessonId)
            ->where('target_start_at', $targetStartAt)
            ->where('target_end_at', $targetEndAt)
            ->where('change_type', 'reschedule')
            ->exists();
    }

    public function nextChangeNo(int $tenantId, int $campusId): string
    {
        return 'CHG' . date('YmdHis') . str_pad((string) $tenantId, 4, '0', \STR_PAD_LEFT) . str_pad((string) $campusId, 4, '0', \STR_PAD_LEFT) . random_int(1000, 9999);
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        (new EducationScopeQuery())->applyTenantCampus($query, $filters, $context);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        foreach (['source_lesson_id', 'target_lesson_id', 'student_id'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, (int) $filters[$column]);
            }
        }
        foreach (['change_type', 'status'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }
        if (isset($filters['keyword']) && $filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $query->where(static function (Builder $query) use ($keyword): void {
                $query->where('change_no', 'like', $keyword)
                    ->orWhere('reason', 'like', $keyword);
            });
        }
    }
}
