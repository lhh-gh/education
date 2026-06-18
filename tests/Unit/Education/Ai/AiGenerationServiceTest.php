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

use App\Model\Education\Ai\EducationAiGenerationTask;
use App\Model\Education\Ai\EducationAiUsageLog;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Ai\AiGenerationService;

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
}
