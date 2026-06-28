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

use App\Model\Education\Academic\EducationLeaveRequest;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationLeaveRequest>
 */
final class LeaveRequestRepository extends IRepository
{
    public function __construct(
        protected readonly EducationLeaveRequest $model
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
        $query->orderByDesc('requested_at')->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationLeaveRequest
    {
        $row = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $row instanceof EducationLeaveRequest ? $row : null;
    }

    public function findByLessonStudent(int $lessonStudentId, int $tenantId): ?EducationLeaveRequest
    {
        $row = $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('lesson_student_id', $lessonStudentId)
            ->first();

        return $row instanceof EducationLeaveRequest ? $row : null;
    }

    public function existsForLessonStudent(int $lessonStudentId, int $tenantId, ?int $excludeId = null): bool
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('lesson_student_id', $lessonStudentId)
            ->when($excludeId !== null, static fn (Builder $query) => $query->where('id', '<>', $excludeId))
            ->exists();
    }

    public function createRequest(array $data): EducationLeaveRequest
    {
        return $this->create($data);
    }

    public function updateStatus(int $id, string $status, array $data, ?int $operatorId): EducationLeaveRequest
    {
        $leave = $this->getQuery()->whereKey($id)->first();
        if (! $leave instanceof EducationLeaveRequest) {
            throw new \RuntimeException('Leave request not found.');
        }
        $data['status'] = $status;
        $data['updated_by'] = $operatorId;
        $leave->fill($data);
        $leave->save();

        return $leave->refresh();
    }

    public function nextLeaveNo(int $tenantId, int $campusId): string
    {
        return 'LEA' . date('YmdHis') . str_pad((string) $tenantId, 4, '0', \STR_PAD_LEFT) . str_pad((string) $campusId, 4, '0', \STR_PAD_LEFT) . random_int(1000, 9999);
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        (new EducationScopeQuery())->applyTenantCampus($query, $filters, $context);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        foreach (['student_id', 'class_id', 'lesson_id'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, (int) $filters[$column]);
            }
        }
        foreach (['source', 'status'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }
        if (isset($filters['keyword']) && $filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $query->where(static function (Builder $query) use ($keyword): void {
                $query->where('leave_no', 'like', $keyword)
                    ->orWhere('reason', 'like', $keyword);
            });
        }
        if (isset($filters['requested_from']) && $filters['requested_from'] !== '') {
            $query->where('requested_at', '>=', Carbon::parse((string) $filters['requested_from'])->toDateTimeString());
        }
        if (isset($filters['requested_to']) && $filters['requested_to'] !== '') {
            $query->where('requested_at', '<=', Carbon::parse((string) $filters['requested_to'])->toDateTimeString());
        }
    }
}
