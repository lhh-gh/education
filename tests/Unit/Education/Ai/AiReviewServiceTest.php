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
use App\Http\Common\ResultCode;
use App\Model\Education\Ai\EducationAiGenerationResult;
use App\Model\Education\Ai\EducationAiGenerationTask;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Ai\AiGenerationService;
use App\Service\Education\Ai\AiReviewService;
use Carbon\Carbon;

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

    public function testApproveRejectsResultOutsideCurrentCampus(): void
    {
        $fixture = $this->aiFixture('ai_review_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-ai-review');
        $hiddenTask = EducationAiGenerationTask::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $hiddenCampus->id,
            'task_no' => uniqid('AIT', true),
            'feature_code' => 'lesson_comment',
            'model_config_id' => $fixture['model_config_id'],
            'prompt_template_id' => $fixture['prompt_template_id'],
            'business_type' => 'lesson_student',
            'business_id' => $fixture['lesson_id'] * 100000000 + $fixture['student_id'],
            'requester_user_id' => $fixture['teacher_user_id'],
            'status' => 'succeeded',
            'context_hash' => hash('sha256', 'hidden-review'),
            'queued_at' => Carbon::now(),
        ]);
        $hiddenResult = EducationAiGenerationResult::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $hiddenCampus->id,
            'generation_task_id' => $hiddenTask->id,
            'result_text' => 'Hidden draft',
            'safety_status' => 'normal',
            'review_status' => 'pending',
            'visible_to_guardian' => false,
        ]);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        try {
            make(AiReviewService::class)->approve((int) $hiddenResult->id, $context, 'reviewed');
            self::fail('Hidden AI result should not be approved.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::NOT_FOUND->value, $exception->getCode());
        }

        self::assertSame('pending', EducationAiGenerationResult::query()->find($hiddenResult->id)->review_status->value);
    }
}
