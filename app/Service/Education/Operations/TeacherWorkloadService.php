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

namespace App\Service\Education\Operations;

use App\Repository\Education\Operations\TeacherWorkloadRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class TeacherWorkloadService
{
    public function __construct(private readonly TeacherWorkloadRepository $repository) {}

    public function recordLessonTeacher(array $data): array
    {
        $record = $this->repository->createOrUpdateLessonTeacherRecord([
            'tenant_id' => (int) $data['tenant_id'],
            'campus_id' => $data['campus_id'] ?? null,
            'teacher_id' => (int) $data['teacher_id'],
            'lesson_id' => (int) $data['lesson_id'],
            'workload_type' => (string) $data['workload_type'],
            'lesson_type' => (string) ($data['lesson_type'] ?? 'normal'),
            'credits' => number_format((float) ($data['credits'] ?? 0), 2, '.', ''),
            'student_count' => (int) ($data['student_count'] ?? 0),
            'present_count' => (int) ($data['present_count'] ?? 0),
            'leave_count' => (int) ($data['leave_count'] ?? 0),
            'absent_count' => (int) ($data['absent_count'] ?? 0),
            'recorded_at' => $data['recorded_at'] ?? Carbon::now()->toDateTimeString(),
            'created_by' => $data['operator_id'] ?? null,
            'updated_by' => $data['operator_id'] ?? null,
        ]);

        return $record->toArray();
    }

    public function summaryByTeacher(int $tenantId, int $teacherId): array
    {
        return $this->repository->summaryByTeacher($tenantId, $teacherId);
    }

    public function report(array $params, EducationUserContext $context): array
    {
        $list = $this->repository->pageReport($params, $context);

        return [
            'list' => $list,
            'total' => \count($list),
            'summary' => isset($params['teacher_id']) && $params['teacher_id'] !== ''
                ? $this->repository->summaryByTeacherContext($context, (int) $params['teacher_id'])
                : [],
        ];
    }

    public function summary(array $params, EducationUserContext $context): array
    {
        return $this->repository->summaryReport($params, $context);
    }
}
