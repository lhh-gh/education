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

namespace App\Service\Education\Growth;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationTrialFeedback;
use App\Model\Education\Admissions\EducationTrialLesson;

final class TrialConversionService
{
    /**
     * @param array<string, mixed> $data
     * @return array{trial_feedback_id: int}
     */
    public function submitTeacherFeedback(int $teacherId, array $data): array
    {
        $trial = EducationTrialLesson::query()->findOrFail((int) $data['trial_lesson_id']);
        if ((int) $trial->teacher_id !== $teacherId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'trial lesson is not assigned to current teacher', ['trial_lesson_id' => (int) $trial->id]);
        }
        $feedback = EducationTrialFeedback::query()->create([
            'tenant_id' => $trial->tenant_id,
            'campus_id' => $trial->campus_id,
            'trial_lesson_id' => $trial->id,
            'lead_id' => $trial->lead_id,
            'feedback_type' => 'teacher_growth',
            'teacher_id' => $teacherId,
            'consultant_user_id' => $trial->consultant_user_id,
            'score' => $data['score'] ?? 0,
            'content' => trim(($data['classroom_performance'] ?? '') . "\n" . ($data['course_recommendation'] ?? '') . "\n" . ($data['teacher_note'] ?? '')),
            'created_by' => $teacherId,
            'updated_by' => $teacherId,
        ]);

        return ['trial_feedback_id' => (int) $feedback->id];
    }

    /**
     * @return array{lead_id: int, conversion_owner: string}
     */
    public function v3ConversionLink(int $leadId): array
    {
        return ['lead_id' => $leadId, 'conversion_owner' => 'v3'];
    }
}
