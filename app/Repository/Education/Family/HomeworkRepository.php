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

namespace App\Repository\Education\Family;

use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Family\EducationHomeworkAssignment;
use App\Model\Education\Family\EducationHomeworkReview;
use App\Model\Education\Family\EducationHomeworkSubmission;
use App\Model\Education\Family\EducationHomeworkTarget;
use App\Service\Education\Foundation\EducationUserContext;

final class HomeworkRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function createAssignment(array $data): EducationHomeworkAssignment
    {
        return EducationHomeworkAssignment::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createTarget(array $data): EducationHomeworkTarget
    {
        return EducationHomeworkTarget::query()->create($data);
    }

    public function findTarget(int $id, int $tenantId): ?EducationHomeworkTarget
    {
        $row = EducationHomeworkTarget::query()->where('tenant_id', $tenantId)->whereKey($id)->first();

        return $row instanceof EducationHomeworkTarget ? $row : null;
    }

    public function findAssignment(int $id, int $tenantId): ?EducationHomeworkAssignment
    {
        $row = EducationHomeworkAssignment::query()->where('tenant_id', $tenantId)->whereKey($id)->first();

        return $row instanceof EducationHomeworkAssignment ? $row : null;
    }

    public function primaryGuardianId(int $tenantId, int $studentId): ?int
    {
        $relation = EducationStudentGuardian::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->orderByDesc('is_primary')
            ->first();

        return $relation instanceof EducationStudentGuardian ? (int) $relation->guardian_id : null;
    }

    public function isGuardianBound(int $tenantId, int $studentId, int $guardianId): bool
    {
        return EducationStudentGuardian::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->where('guardian_id', $guardianId)
            ->exists();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createSubmission(array $data): EducationHomeworkSubmission
    {
        return EducationHomeworkSubmission::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateTarget(EducationHomeworkTarget $target, array $data): EducationHomeworkTarget
    {
        $target->fill($data);
        $target->save();

        return $target;
    }

    public function findSubmission(int $id, int $tenantId): ?EducationHomeworkSubmission
    {
        $row = EducationHomeworkSubmission::query()->where('tenant_id', $tenantId)->whereKey($id)->first();

        return $row instanceof EducationHomeworkSubmission ? $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createReview(array $data): EducationHomeworkReview
    {
        return EducationHomeworkReview::query()->create($data);
    }

    public function isTeacherAssignedToSubmission(EducationHomeworkSubmission $submission, int $teacherId): bool
    {
        $target = $this->findTarget((int) $submission->homework_target_id, (int) $submission->tenant_id);
        if (! $target instanceof EducationHomeworkTarget) {
            return false;
        }
        $assignment = $this->findAssignment((int) $target->homework_assignment_id, (int) $submission->tenant_id);
        if (! $assignment instanceof EducationHomeworkAssignment || $assignment->lesson_id === null) {
            return false;
        }

        return EducationLesson::query()
            ->where('tenant_id', (int) $submission->tenant_id)
            ->where('id', (int) $assignment->lesson_id)
            ->where('teacher_id', $teacherId)
            ->exists();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageAssignments(array $filters, EducationUserContext $context): array
    {
        $query = EducationHomeworkAssignment::query()->where('tenant_id', $context->tenantId);
        if (($filters['campus_id'] ?? '') !== '') {
            $query->where('campus_id', (int) $filters['campus_id']);
        }
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', (string) $filters['status']);
        }
        $total = (clone $query)->count();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationHomeworkAssignment $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
