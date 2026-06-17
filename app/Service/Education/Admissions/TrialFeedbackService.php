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

namespace App\Service\Education\Admissions;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationTrialLesson;
use App\Repository\Education\Admissions\TrialFeedbackRepository;
use App\Repository\Education\Admissions\TrialLessonRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class TrialFeedbackService
{
    public function __construct(
        private readonly TrialLessonRepository $lessonRepository,
        private readonly TrialFeedbackRepository $repository
    ) {}

    public function create(array $data, EducationUserContext $context): array
    {
        $lesson = $this->lessonRepository->findScoped((int) $data['trial_lesson_id'], $context);
        if (! $lesson instanceof EducationTrialLesson) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'trial lesson not found in current context', ['trial_lesson_id' => (int) $data['trial_lesson_id']]);
        }

        $feedback = $this->repository->create([
            'tenant_id' => $context->tenantId,
            'campus_id' => $lesson->campus_id,
            'trial_lesson_id' => (int) $lesson->id,
            'lead_id' => (int) $lesson->lead_id,
            'feedback_type' => $data['feedback_type'] ?? 'teacher',
            'teacher_id' => $data['teacher_id'] ?? $lesson->teacher_id,
            'consultant_user_id' => $data['consultant_user_id'] ?? $lesson->consultant_user_id,
            'score' => $data['score'] ?? null,
            'content' => trim((string) $data['content']),
            'recommend_course_id' => $data['recommend_course_id'] ?? null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return $feedback->refresh()->toArray();
    }
}
