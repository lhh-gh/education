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
use App\Model\Education\Ai\EducationAiSafetyEvent;
use App\Model\Education\Ai\EducationAiUsageLog;

/**
 * @internal
 * @coversNothing
 */
final class AiAdminApiTest extends AiApiCase
{
    public function testAdminAiEndpointsValidateAndReturnCatalogResponses(): void
    {
        $fixture = $this->aiFixture('ai_admin_api');
        $this->grantPermissions(
            'education:ai:model-config:save',
            'education:ai:prompt:save',
            'education:ai:data-question:create',
            'education:ai:usage:summary',
            'education:ai:safety:page'
        );
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);

        $invalidConfig = $this->post('/admin/education/ai/model-configs', [
            'config_code' => 'default-gpt',
            'provider' => 'openai',
        ], $headers);
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalidConfig['code']);
        self::assertSame('model_name is required', $invalidConfig['message']);

        $config = $this->post('/admin/education/ai/model-configs', [
            'config_code' => 'default-gpt',
            'provider' => 'openai',
            'model_name' => 'gpt-4.1-mini',
            'api_key' => 'secret-key',
            'status' => 'enabled',
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $config['code']);
        self::assertFalse($config['data']['api_key_visible']);

        $prompt = $this->post('/admin/education/ai/prompt-templates', [
            'template_code' => 'lesson-comment',
            'feature_code' => 'lesson_comment',
            'template_name' => 'Lesson comment',
            'version' => 2,
            'system_prompt' => 'safe system',
            'user_prompt' => 'safe user',
            'status' => 'draft',
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $prompt['code']);
        self::assertSame('draft', $prompt['data']['status']);

        $rawSql = $this->post('/admin/education/ai/data-questions', [
            'question_text' => 'select * from edu_orders',
            'metric_codes' => ['renewal_alert_count'],
        ], $headers);
        self::assertSame(ResultCode::FORBIDDEN->value, $rawSql['code']);

        EducationAiUsageLog::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'provider' => 'fake',
            'model_name' => 'fake-model',
            'prompt_tokens' => 10,
            'completion_tokens' => 20,
            'total_tokens' => 30,
            'cost_cents' => 9,
            'usage_date' => '2026-06-10',
        ]);
        $usage = $this->get('/admin/education/ai/usage/summary', [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $usage['code']);
        self::assertSame(30, $usage['data']['total_tokens']);
        self::assertSame(9, $usage['data']['cost_cents']);

        EducationAiSafetyEvent::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'risk_level' => 'high',
            'event_type' => 'unsafe_output',
            'summary' => 'Unsafe output',
            'payload_json' => ['blocked' => true],
            'handled' => false,
        ]);
        $safety = $this->get('/admin/education/ai/safety-events/page', ['risk_level' => 'high'], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $safety['code']);
        self::assertSame(1, $safety['data']['total']);
        self::assertSame('unsafe_output', $safety['data']['list'][0]['event_type']);
    }
}
