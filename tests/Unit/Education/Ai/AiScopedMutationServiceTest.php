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
use App\Model\Education\Ai\EducationAiRecommendationTask;
use App\Model\Education\Ai\EducationAiSafetyEvent;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Ai\AiRecommendationService;
use App\Service\Education\Ai\AiSafetyService;

/**
 * @internal
 * @coversNothing
 */
final class AiScopedMutationServiceTest extends AiTestCase
{
    public function testRecommendationHandleRejectsTaskOutsideCurrentCampus(): void
    {
        $fixture = $this->aiFixture('ai_recommendation_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-ai-recommendation');
        $task = EducationAiRecommendationTask::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $hiddenCampus->id,
            'recommendation_type' => 'renewal',
            'target_type' => 'student',
            'target_id' => $fixture['student_id'],
            'assignee_user_id' => $fixture['teacher_user_id'],
            'status' => 'pending',
            'recommendation_json' => ['summary' => 'hidden'],
        ]);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        try {
            make(AiRecommendationService::class)->markHandled((int) $task->id, $context);
            self::fail('Hidden recommendation should not be handled.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::NOT_FOUND->value, $exception->getCode());
        }

        self::assertSame('pending', (string) $task->refresh()->status);
    }

    public function testSafetyHandleRejectsEventOutsideCurrentCampus(): void
    {
        $fixture = $this->aiFixture('ai_safety_scope');
        $tenant = EducationTenant::query()->findOrFail($fixture['tenant_id']);
        $hiddenCampus = $this->campus($tenant, 'hidden-ai-safety');
        $event = EducationAiSafetyEvent::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $hiddenCampus->id,
            'generation_task_id' => null,
            'risk_level' => 'blocked',
            'event_type' => 'unsafe_content',
            'summary' => 'hidden',
            'payload_json' => ['text' => 'hidden'],
            'handled' => false,
        ]);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        try {
            make(AiSafetyService::class)->markHandled((int) $event->id, $context);
            self::fail('Hidden safety event should not be handled.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::NOT_FOUND->value, $exception->getCode());
        }

        self::assertFalse((bool) $event->refresh()->handled);
    }
}
