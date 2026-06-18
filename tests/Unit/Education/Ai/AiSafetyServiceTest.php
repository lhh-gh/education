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
use App\Model\Education\Ai\EducationAiSafetyEvent;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Ai\AiGenerationService;
use App\Service\Education\Ai\AiSafetyService;

/**
 * @internal
 * @coversNothing
 */
final class AiSafetyServiceTest extends AiTestCase
{
    public function testUnsafeOutputIsBlockedAndLogged(): void
    {
        $fixture = $this->aiFixture('ai_safety');
        $generation = make(AiGenerationService::class);
        $safety = make(AiSafetyService::class);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);
        $task = $generation->requestLessonCommentDraft(['lesson_id' => $fixture['lesson_id'], 'student_id' => $fixture['student_id']], $context);

        $event = $safety->blockTask($task['task_id'], 'unsafe_output', 'Unsafe output', ['reason' => 'policy']);

        self::assertSame('blocked', EducationAiGenerationTask::query()->find($task['task_id'])->status->value);
        self::assertTrue(EducationAiSafetyEvent::query()->where('id', $event['safety_event_id'])->where('event_type', 'unsafe_output')->exists());
    }
}
