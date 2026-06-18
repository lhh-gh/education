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

use App\Model\Education\Ai\EducationAiPromptTemplate;

final class PromptTemplateRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationAiPromptTemplate
    {
        return EducationAiPromptTemplate::query()->updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'template_code' => $data['template_code'], 'version' => $data['version'] ?? 1],
            $data
        );
    }

    public function publishedForFeature(int $tenantId, string $featureCode): ?EducationAiPromptTemplate
    {
        return EducationAiPromptTemplate::query()
            ->where('tenant_id', $tenantId)
            ->where('feature_code', $featureCode)
            ->where('status', 'published')
            ->orderByDesc('version')
            ->first();
    }

    public function byCodeVersion(int $tenantId, string $templateCode, int $version): ?EducationAiPromptTemplate
    {
        return EducationAiPromptTemplate::query()
            ->where('tenant_id', $tenantId)
            ->where('template_code', $templateCode)
            ->where('version', $version)
            ->first();
    }
}
