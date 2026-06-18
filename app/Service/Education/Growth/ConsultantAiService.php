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

namespace App\Service\Education\Growth;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationLead;
use App\Repository\Education\Growth\AiTalkScriptRepository;
use Carbon\Carbon;

final class ConsultantAiService
{
    public function __construct(private readonly AiTalkScriptRepository $scripts) {}

    /**
     * @param array<string, mixed> $data
     * @return array{ai_talk_script_id: int, status: string}
     */
    public function generateScript(array $data): array
    {
        $text = (string) ($data['generated_text'] ?? 'Please invite the guardian to a trial lesson after consultant review.');
        $this->assertSafeScript($text);
        $lead = EducationLead::query()->where('tenant_id', $data['tenant_id'])->findOrFail((int) $data['lead_id']);
        $script = $this->scripts->create([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'] ?? $lead->campus_id,
            'lead_id' => $lead->id,
            'generation_task_id' => $data['generation_task_id'] ?? null,
            'script_type' => (string) $data['script_type'],
            'script_text' => $text,
            'status' => 'draft',
            'masked_input_json' => [
                'guardian_name' => $lead->contact_name,
                'guardian_mobile' => $this->maskMobile((string) $lead->contact_mobile),
                'goal' => $data['goal'] ?? null,
            ],
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['created_by'] ?? null,
        ]);

        return ['ai_talk_script_id' => (int) $script->id, 'status' => 'draft'];
    }

    /**
     * @return array{ai_talk_script_id: int, status: string}
     */
    public function confirmScript(int $id, int $userId, string $editedScript): array
    {
        $this->assertSafeScript($editedScript);
        $script = $this->scripts->script($id);
        if ((string) $script->status->value !== 'draft') {
            throw new BusinessException(ResultCode::CONFLICT, 'ai talk script is not draft', ['id' => $id, 'status' => (string) $script->status->value]);
        }
        $script->script_text = $editedScript;
        $script->status = 'confirmed';
        $script->confirmed_by = $userId;
        $script->confirmed_at = Carbon::now();
        $script->updated_by = $userId;
        $script->save();

        return ['ai_talk_script_id' => $id, 'status' => 'confirmed'];
    }

    private function assertSafeScript(string $text): void
    {
        foreach (['guaranteed discount', 'automatic discount', 'promise discount', 'create payment order'] as $blocked) {
            if (mb_stripos($text, $blocked) !== false) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'ai script contains automatic discount promise', ['blocked_phrase' => $blocked]);
            }
        }
    }

    private function maskMobile(string $mobile): string
    {
        if (mb_strlen($mobile) < 8) {
            return '****';
        }

        return mb_substr($mobile, 0, 3) . '****' . mb_substr($mobile, -4);
    }
}
