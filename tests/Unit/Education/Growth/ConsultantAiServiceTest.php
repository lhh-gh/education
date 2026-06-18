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

namespace HyperfTests\Unit\Education\Growth;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Growth\EducationGrowthAiTalkScript;
use App\Service\Education\Growth\ConsultantAiService;

/**
 * @internal
 * @coversNothing
 */
final class ConsultantAiServiceTest extends GrowthTestCase
{
    public function testAiScriptInputMasksGuardianPrivateFields(): void
    {
        [$tenant, $campus] = $this->tenantCampus('growth_ai_mask');
        $lead = $this->leadFixture($tenant, $campus, [
            'contact_name' => 'Guardian Zhang',
            'contact_mobile' => '13812345678',
            'owner_user_id' => 203,
        ]);

        $result = make(ConsultantAiService::class)->generateScript([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lead_id' => $lead->id,
            'script_type' => 'trial_invitation',
            'goal' => 'invite trial lesson',
            'generated_text' => 'Invite guardian to a trial lesson.',
            'created_by' => 203,
        ]);
        $script = EducationGrowthAiTalkScript::query()->findOrFail($result['ai_talk_script_id']);

        self::assertSame('draft', $result['status']);
        self::assertSame('138****5678', $script->masked_input_json['guardian_mobile']);
        self::assertStringNotContainsString('13812345678', json_encode($script->masked_input_json, \JSON_THROW_ON_ERROR));
    }

    public function testAiScriptBlocksAutomaticDiscountPromise(): void
    {
        [$tenant, $campus] = $this->tenantCampus('growth_ai_block');
        $lead = $this->leadFixture($tenant, $campus, ['owner_user_id' => 204]);

        try {
            make(ConsultantAiService::class)->generateScript([
                'tenant_id' => $tenant->id,
                'campus_id' => $campus->id,
                'lead_id' => $lead->id,
                'script_type' => 'trial_invitation',
                'goal' => 'invite trial lesson',
                'generated_text' => 'We can promise a guaranteed discount today.',
                'created_by' => 204,
            ]);
            self::fail('Automatic discount promises should be blocked.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $exception->getCode());
            self::assertSame('ai script contains automatic discount promise', $exception->getMessage());
        }
    }
}
