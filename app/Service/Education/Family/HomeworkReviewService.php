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
use App\Model\Education\Family\EducationHomeworkSubmission;
use App\Model\Education\Family\EducationHomeworkTarget;
use App\Repository\Education\Family\HomeworkRepository;
use App\Service\Education\Academic\TeacherMobileContextResolver;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class HomeworkReviewService
{
    public function __construct(
        private readonly HomeworkRepository $repository,
        private readonly TeacherMobileContextResolver $teacherResolver,
        private readonly ServiceQualityService $qualityService
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{homework_review_id: int, target_status: string}
     */
    public function review(array $data, EducationUserContext $context): array
    {
        $teacher = $this->teacherResolver->resolveTeacher($context);
        $submission = $this->repository->findSubmission((int) ($data['homework_submission_id'] ?? 0), (int) $context->tenantId);
        if (! $submission instanceof EducationHomeworkSubmission) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'homework submission not found', ['homework_submission_id' => (int) ($data['homework_submission_id'] ?? 0)]);
        }
        if (! $this->repository->isTeacherAssignedToSubmission($submission, (int) $teacher->id)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher cannot review this submission', ['homework_submission_id' => (int) $submission->id]);
        }

        return Db::transaction(function () use ($data, $context, $submission, $teacher): array {
            $review = $this->repository->createReview([
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => $submission->campus_id,
                'homework_submission_id' => (int) $submission->id,
                'teacher_id' => (int) $teacher->id,
                'score' => isset($data['score']) ? (int) $data['score'] : null,
                'content' => (string) ($data['content'] ?? ''),
                'reviewed_at' => Carbon::now(),
                'status' => 'reviewed',
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $target = $this->repository->findTarget((int) $submission->homework_target_id, (int) $context->tenantId);
            if ($target instanceof EducationHomeworkTarget) {
                $this->repository->updateTarget($target, [
                    'status' => 'reviewed',
                    'reviewed_at' => Carbon::now(),
                    'updated_by' => $context->userId,
                ]);
            }
            $this->qualityService->incrementHomeworkReview((int) $context->tenantId, $submission->campus_id, (int) $teacher->id, (int) $submission->student_id);

            return ['homework_review_id' => (int) $review->id, 'target_status' => 'reviewed'];
        });
    }
}
