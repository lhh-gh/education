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

use App\Model\Education\Academic\EducationLesson;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationLesson>
 */
final class LessonRepository extends IRepository
{
    public function __construct(
        protected readonly EducationLesson $model
    ) {}

    public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters);
        $query->orderByDesc('start_at')->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function calendar(array $filters, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters);
        $query->orderBy('start_at')->orderBy('id');

        return $query->get()->toArray();
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationLesson
    {
        $lesson = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $lesson instanceof EducationLesson ? $lesson : null;
    }

    public function createScheduled(array $data): EducationLesson
    {
        return $this->create($data);
    }

    public function updateScheduled(int $id, array $data, ?int $operatorId): EducationLesson
    {
        $lesson = $this->getQuery()->whereKey($id)->first();
        if (! $lesson instanceof EducationLesson) {
            throw new \RuntimeException('Lesson not found.');
        }
        $data['updated_by'] = $operatorId;
        $lesson->fill($data);
        $lesson->save();

        return $lesson->refresh();
    }

    public function cancel(int $id, string $reason, ?int $operatorId): EducationLesson
    {
        return $this->updateScheduled($id, [
            'status' => 'cancelled',
            'cancel_reason' => $reason,
            'cancelled_at' => Carbon::now()->toDateTimeString(),
        ], $operatorId);
    }

    public function nextLessonNo(int $tenantId, int $campusId): string
    {
        $prefix = 'L' . date('Ymd') . str_pad((string) $tenantId, 4, '0', \STR_PAD_LEFT) . str_pad((string) $campusId, 4, '0', \STR_PAD_LEFT);
        $count = $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('lesson_no', 'like', $prefix . '%')
            ->count();

        return $prefix . str_pad((string) ($count + 1), 4, '0', \STR_PAD_LEFT);
    }

    public function overlappingLessons(array $filters, ?int $excludeLessonId = null): array
    {
        $query = $this->getQuery()
            ->where('tenant_id', (int) $filters['tenant_id'])
            ->where('campus_id', (int) $filters['campus_id'])
            ->where('status', '<>', 'cancelled')
            ->where('start_at', '<', (string) $filters['end_at'])
            ->where('end_at', '>', (string) $filters['start_at'])
            ->when($excludeLessonId !== null, static fn (Builder $query) => $query->where('id', '<>', $excludeLessonId));

        foreach (['teacher_id', 'classroom_id', 'class_id'] as $column) {
            if (\array_key_exists($column, $filters) && $filters[$column] !== null && $filters[$column] !== '') {
                $query->where($column, (int) $filters[$column]);
            }
        }

        return $query->orderBy('start_at')->get()->toArray();
    }

    public function hasAttendanceOrConsumptionReferences(int $id, int $tenantId): bool
    {
        return false;
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        (new EducationScopeQuery())->applyTenantCampus($query, $filters, $context);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        foreach (['class_id', 'course_id', 'teacher_id', 'classroom_id'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, (int) $filters[$column]);
            }
        }
        foreach (['status', 'source_type', 'schedule_batch_no'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }
        if (isset($filters['start_at']) && $filters['start_at'] !== '') {
            $query->where('end_at', '>', (string) $filters['start_at']);
        }
        if (isset($filters['end_at']) && $filters['end_at'] !== '') {
            $query->where('start_at', '<', (string) $filters['end_at']);
        }
    }
}
