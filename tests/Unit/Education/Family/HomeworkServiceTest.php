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

namespace HyperfTests\Unit\Education\Family;

use App\Model\Education\Family\EducationHomeworkTarget;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Family\HomeworkReviewService;
use App\Service\Education\Family\HomeworkService;

/**
 * @internal
 * @coversNothing
 */
final class HomeworkServiceTest extends FamilyTestCase
{
    public function testHomeworkAssignmentSubmissionReviewFlow(): void
    {
        $fixture = $this->familyFixture('family_homework');
        $service = make(HomeworkService::class);
        $reviewService = make(HomeworkReviewService::class);

        $assignment = $service->publishAssignment([
            'campus_id' => $fixture['campus_id'],
            'title' => 'Unit 1 practice',
            'content' => 'Finish worksheet',
            'course_id' => $fixture['course_id'],
            'class_id' => $fixture['class_id'],
            'lesson_id' => $fixture['lesson_id'],
            'due_at' => '2026-06-15 20:00:00',
            'student_ids' => [$fixture['student_id']],
        ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']], userId: 7101));

        $target = EducationHomeworkTarget::query()->where('homework_assignment_id', $assignment['homework_assignment_id'])->first();
        self::assertSame('assigned', (string) $target->status->value);

        $submission = $service->submitByGuardian([
            'homework_target_id' => (int) $target->id,
            'student_id' => $fixture['student_id'],
            'content' => 'Submitted',
        ], $this->context($fixture['tenant_id'], EducationRoleCode::Guardian, [$fixture['campus_id']], $fixture['guardian_user_id']));

        self::assertSame('submitted', $submission['status']);
        self::assertSame('submitted', (string) $target->refresh()->status->value);

        $review = $reviewService->review([
            'homework_submission_id' => $submission['homework_submission_id'],
            'score' => 90,
            'content' => 'Good work',
        ], $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']));

        self::assertSame('reviewed', $review['target_status']);
        self::assertSame('reviewed', (string) $target->refresh()->status->value);
    }
}
