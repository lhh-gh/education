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

namespace HyperfTests\Feature\Education\Ai;

use App\Http\Common\ResultCode;
use App\Model\Education\Ai\EducationAiGenerationResult;
use App\Model\Education\Ai\EducationAiGenerationTask;
use App\Model\Education\Foundation\EducationAuditLog;
use Carbon\Carbon;

/**
 * @internal
 * @coversNothing
 */
final class AiPermissionIsolationAuditTest extends AiApiCase
{
    public function testAiMutationsRequirePermissionAndWriteAudit(): void
    {
        $fixture = $this->aiFixture('ai_audit_api');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);
        $payload = [
            'config_code' => 'audit-gpt',
            'provider' => 'openai',
            'model_name' => 'gpt-4.1-mini',
            'api_key' => 'secret-key',
            'status' => 'enabled',
        ];

        $denied = $this->post('/admin/education/ai/model-configs', $payload, $headers);
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);

        $this->grantPermissions('education:ai:model-config:save');
        $allowed = $this->post('/admin/education/ai/model-configs', $payload, $headers);

        self::assertSame(ResultCode::SUCCESS->value, $allowed['code']);
        self::assertSame(1, EducationAuditLog::query()->where('module', 'ai')->where('action', 'education.ai.config.saved')->count());
    }

    public function testBlockedAiResultCannotBeApproved(): void
    {
        $fixture = $this->aiFixture('ai_blocked_api');
        $this->grantPermissions('education:ai:review:approve');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);
        $task = EducationAiGenerationTask::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'task_no' => uniqid('AIT', true),
            'feature_code' => 'lesson_comment',
            'model_config_id' => $fixture['model_config_id'],
            'prompt_template_id' => $fixture['prompt_template_id'],
            'business_type' => 'lesson_student',
            'business_id' => $fixture['lesson_id'] * 100000000 + $fixture['student_id'],
            'requester_user_id' => $this->user->id,
            'status' => 'blocked',
            'context_hash' => hash('sha256', 'blocked'),
            'queued_at' => Carbon::now(),
        ]);
        $result = EducationAiGenerationResult::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'generation_task_id' => $task->id,
            'result_text' => 'unsafe draft',
            'safety_status' => 'blocked',
            'review_status' => 'pending',
            'visible_to_guardian' => false,
        ]);

        $approved = $this->post('/admin/education/ai/generation-results/' . $result->id . '/approve', [
            'review_note' => 'looks fine',
        ], $headers);

        self::assertSame(ResultCode::CONFLICT->value, $approved['code']);
        self::assertSame('ai result was blocked by safety policy', $approved['message']);
    }
}
