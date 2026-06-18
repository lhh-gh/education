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

namespace App\Repository\Education\Admissions;

use App\Model\Education\Admissions\EducationTrialLesson;
use App\Service\Education\Foundation\EducationUserContext;

final class TrialLessonRepository
{
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = EducationTrialLesson::query()->where('tenant_id', $context->tenantId);
        if (isset($filters['campus_id']) && $filters['campus_id'] !== '') {
            $query->where('campus_id', (int) $filters['campus_id']);
        } elseif ($context->campusIds !== []) {
            $query->whereIn('campus_id', $context->campusIds);
        }
        foreach (['status', 'teacher_id', 'lead_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        if (isset($filters['date']) && $filters['date'] !== '') {
            $query->where('start_time', '>=', $filters['date'] . ' 00:00:00')
                ->where('start_time', '<=', $filters['date'] . ' 23:59:59');
        }

        return $this->paginate($query->orderBy('start_time'), $filters);
    }

    public function findConflict(int $tenantId, int $teacherId, ?int $classroomId, string $startTime, string $endTime, ?int $exceptId = null): ?EducationTrialLesson
    {
        $query = EducationTrialLesson::query()
            ->where('tenant_id', $tenantId)
            ->where('status', '<>', 'cancelled')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->where(static function ($query) use ($teacherId, $classroomId): void {
                $query->where('teacher_id', $teacherId);
                if ($classroomId !== null) {
                    $query->orWhere('classroom_id', $classroomId);
                }
            });
        if ($exceptId !== null) {
            $query->where('id', '<>', $exceptId);
        }
        $lesson = $query->first();

        return $lesson instanceof EducationTrialLesson ? $lesson : null;
    }

    public function create(array $data): EducationTrialLesson
    {
        return EducationTrialLesson::query()->create($data);
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationTrialLesson
    {
        $query = EducationTrialLesson::query()->where('tenant_id', $context->tenantId)->whereKey($id);
        if ($context->campusIds !== []) {
            $query->whereIn('campus_id', $context->campusIds);
        }
        $lesson = $query->first();

        return $lesson instanceof EducationTrialLesson ? $lesson : null;
    }

    private function paginate(mixed $query, array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->forPage($page, $pageSize)->get()->map(static fn ($row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
