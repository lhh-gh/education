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
use App\Model\Education\Ai\EducationAiUsageLog;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Ai\AiGenerationService;
use Carbon\Carbon;

/**
 * @internal
 * @coversNothing
 */
final class AiGenerationServiceTest extends AiTestCase
{
    public function testGenerationTaskIsQueuedAndUsageLogged(): void
    {
        $fixture = $this->aiFixture('ai_generation');
        $service = make(AiGenerationService::class);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        $task = $service->requestLessonCommentDraft([
            'lesson_id' => $fixture['lesson_id'],
            'student_id' => $fixture['student_id'],
            'keywords' => ['active', 'needs practice'],
        ], $context);

        self::assertSame('queued', $task['status']);
        self::assertTrue(EducationAiGenerationTask::query()->where('id', $task['task_id'])->exists());

        $service->storeSuccessfulResult($task['task_id'], 'Draft text', ['tone' => 'warm'], 10, 20, 3);

        self::assertTrue(EducationAiUsageLog::query()->where('generation_task_id', $task['task_id'])->where('total_tokens', 30)->exists());
    }

    public function testResultDetailRejectsResultOutsideCurrentCampus(): void
    {
        $fixture = $this->aiFixture('ai_generation_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-ai-generation');
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
            'context_hash' => hash('sha256', 'hidden'),
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

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(ResultCode::NOT_FOUND->value);

        make(AiGenerationService::class)->resultDetail((int) $hiddenResult->id, $context);
    }
}
