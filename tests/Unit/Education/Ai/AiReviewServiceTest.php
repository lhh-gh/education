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

namespace HyperfTests\Unit\Education\Ai;

use App\Exception\BusinessException;
use App\Model\Education\Ai\EducationAiGenerationResult;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Ai\AiGenerationService;
use App\Service\Education\Ai\AiReviewService;

/**
 * @internal
 * @coversNothing
 */
final class AiReviewServiceTest extends AiTestCase
{
    public function testUnreviewedResultCannotBeGuardianVisible(): void
    {
        $fixture = $this->aiFixture('ai_review');
        $generation = make(AiGenerationService::class);
        $review = make(AiReviewService::class);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);
        $task = $generation->requestLessonCommentDraft([
            'lesson_id' => $fixture['lesson_id'],
            'student_id' => $fixture['student_id'],
            'keywords' => ['steady'],
        ], $context);
        $result = $generation->storeSuccessfulResult($task['task_id'], 'Draft text');

        self::assertFalse((bool) EducationAiGenerationResult::query()->find($result['generation_result_id'])->visible_to_guardian);

        $approved = $review->approve($result['generation_result_id'], $context, 'reviewed');

        self::assertSame('approved', $approved['review_status']);
        self::assertFalse((bool) EducationAiGenerationResult::query()->find($result['generation_result_id'])->visible_to_guardian);
    }

    public function testBlockedResultCannotBeApproved(): void
    {
        $fixture = $this->aiFixture('ai_review_blocked');
        $generation = make(AiGenerationService::class);
        $review = make(AiReviewService::class);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);
        $task = $generation->requestLessonCommentDraft(['lesson_id' => $fixture['lesson_id'], 'student_id' => $fixture['student_id']], $context);
        $result = $generation->storeBlockedResult($task['task_id'], 'unsafe');

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(409);
        $review->approve($result['generation_result_id'], $context);
    }
}
