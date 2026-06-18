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

namespace App\Repository\Education\Ai;

use App\Model\Education\Ai\EducationAiFeatureSetting;
use App\Model\Education\Ai\EducationAiModelConfig;

final class AiConfigRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function saveModelConfig(array $data): EducationAiModelConfig
    {
        return EducationAiModelConfig::query()->updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'config_code' => $data['config_code']],
            $data
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveFeatureSetting(array $data): EducationAiFeatureSetting
    {
        return EducationAiFeatureSetting::query()->updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'feature_code' => $data['feature_code']],
            $data
        );
    }

    public function enabledFeature(int $tenantId, string $featureCode): ?EducationAiFeatureSetting
    {
        return EducationAiFeatureSetting::query()
            ->where('tenant_id', $tenantId)
            ->where('feature_code', $featureCode)
            ->where('enabled', true)
            ->first();
    }

    public function modelConfig(int $tenantId, int $id): ?EducationAiModelConfig
    {
        return EducationAiModelConfig::query()
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();
    }
}
