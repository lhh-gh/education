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

namespace App\Service\Education\Family;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Family\EducationHomeworkTarget;
use App\Repository\Education\Family\HomeworkRepository;
use App\Service\Education\Academic\GuardianMobileContextResolver;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class HomeworkService
{
    public function __construct(
        private readonly HomeworkRepository $repository,
        private readonly GuardianMobileContextResolver $guardianResolver
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{homework_assignment_id: int, target_count: int, status: string}
     */
    public function publishAssignment(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $assignment = $this->repository->createAssignment([
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => (int) ($data['campus_id'] ?? $context->currentCampusId),
                'title' => (string) ($data['title'] ?? ''),
                'content' => (string) ($data['content'] ?? ''),
                'course_id' => isset($data['course_id']) ? (int) $data['course_id'] : null,
                'class_id' => isset($data['class_id']) ? (int) $data['class_id'] : null,
                'lesson_id' => isset($data['lesson_id']) ? (int) $data['lesson_id'] : null,
                'status' => 'published',
                'publish_at' => Carbon::now(),
                'due_at' => $data['due_at'] ?? null,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            $targetCount = 0;
            foreach (array_values(array_unique(array_map('intval', (array) ($data['student_ids'] ?? [])))) as $studentId) {
                $this->repository->createTarget([
                    'tenant_id' => (int) $context->tenantId,
                    'campus_id' => $assignment->campus_id,
                    'homework_assignment_id' => (int) $assignment->id,
                    'student_id' => $studentId,
                    'guardian_id' => $this->repository->primaryGuardianId((int) $context->tenantId, $studentId),
                    'status' => 'assigned',
                    'created_by' => $context->userId,
                    'updated_by' => $context->userId,
                ]);
                ++$targetCount;
            }

            return ['homework_assignment_id' => (int) $assignment->id, 'target_count' => $targetCount, 'status' => 'published'];
        });
    }

    /**
     * @param array<string, mixed> $data
     * @return array{homework_submission_id: int, status: string}
     */
    public function submitByGuardian(array $data, EducationUserContext $context): array
    {
        $guardian = $this->guardianResolver->resolveGuardian($context);
        $tenantId = (int) $context->tenantId;
        $target = $this->repository->findTarget((int) ($data['homework_target_id'] ?? 0), $tenantId);
        if (! $target instanceof EducationHomeworkTarget) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'homework target not found', ['homework_target_id' => (int) ($data['homework_target_id'] ?? 0)]);
        }
        $studentId = (int) ($data['student_id'] ?? $target->student_id);
        if ($studentId !== (int) $target->student_id || ! $this->repository->isGuardianBound($tenantId, $studentId, (int) $guardian->id)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'student is not bound to current guardian', ['student_id' => $studentId]);
        }

        return Db::transaction(function () use ($data, $context, $target, $studentId, $guardian): array {
            $submission = $this->repository->createSubmission([
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => $target->campus_id,
                'homework_target_id' => (int) $target->id,
                'student_id' => $studentId,
                'guardian_id' => (int) $guardian->id,
                'content' => (string) ($data['content'] ?? ''),
                'attachment_count' => \count((array) ($data['attachment_ids'] ?? [])),
                'submitted_at' => Carbon::now(),
                'status' => 'submitted',
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $this->repository->updateTarget($target, [
                'status' => 'submitted',
                'submitted_at' => Carbon::now(),
                'updated_by' => $context->userId,
            ]);

            return ['homework_submission_id' => (int) $submission->id, 'status' => 'submitted'];
        });
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageAssignments(array $filters, EducationUserContext $context): array
    {
        return $this->repository->pageAssignments($filters, $context);
    }
}
