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

namespace HyperfTests\Feature\Education\Growth;

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Education\Growth\EducationGrowthAiTalkScript;

/**
 * @internal
 * @coversNothing
 */
final class GrowthAdminApiTest extends GrowthApiCase
{
    public function testGrowthMutationsRequirePermissionAndWriteAudit(): void
    {
        $fixture = $this->growthFixture('growth_score_api');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);

        $denied = $this->post('/admin/education/growth/leads/' . $fixture['lead_id'] . '/score/recalculate', ['reason' => 'after trial feedback'], $headers);
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);

        $this->grantPermissions('education:growth:score:recalculate');
        $ok = $this->post('/admin/education/growth/leads/' . $fixture['lead_id'] . '/score/recalculate', ['reason' => 'after trial feedback'], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $ok['code']);
        self::assertSame($fixture['lead_id'], $ok['data']['lead_id']);
        self::assertSame(1, EducationAuditLog::query()->where('module', 'growth')->where('action', 'education.growth.score.recalculated')->count());
    }

    public function testApiFailuresMatchCatalogForAiScripts(): void
    {
        $fixture = $this->growthFixture('growth_ai_api');
        $this->grantPermissions('education:growth:ai-script:generate', 'education:growth:ai-script:confirm');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);

        $invalid = $this->post('/admin/education/growth/ai-talk-scripts/generate', ['lead_id' => $fixture['lead_id']], $headers);
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalid['code']);
        self::assertSame('script_type is required', $invalid['message']);

        $blocked = $this->post('/admin/education/growth/ai-talk-scripts/generate', [
            'lead_id' => $fixture['lead_id'],
            'script_type' => 'trial_invitation',
            'goal' => 'invite trial lesson',
            'generated_text' => 'guaranteed discount today',
        ], $headers);
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $blocked['code']);
        self::assertSame('ai script contains automatic discount promise', $blocked['message']);

        $script = EducationGrowthAiTalkScript::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lead_id' => $fixture['lead_id'],
            'script_type' => 'trial_invitation',
            'script_text' => 'used script',
            'status' => 'used',
            'masked_input_json' => [],
        ]);
        $conflict = $this->post('/admin/education/growth/ai-talk-scripts/' . $script->id . '/confirm', ['edited_script' => 'hello'], $headers);
        self::assertSame(ResultCode::CONFLICT->value, $conflict['code']);
    }
}
