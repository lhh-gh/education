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

namespace App\Service\Education\Ai;

use App\Model\Education\Ai\EducationAiPromptTemplate;
use App\Repository\Education\Ai\PromptTemplateRepository;
use Carbon\Carbon;

final class PromptTemplateService
{
    public function __construct(private readonly PromptTemplateRepository $repository) {}

    /**
     * @param array<string, mixed> $data
     * @return array{id: int, status: string}
     */
    public function save(array $data): array
    {
        $prompt = $this->repository->save($data);

        return ['id' => (int) $prompt->id, 'status' => (string) $prompt->status];
    }

    /**
     * @return array{id: int, status: string}
     */
    public function publish(int $tenantId, string $templateCode, int $version = 1): array
    {
        $prompt = $this->repository->byCodeVersion($tenantId, $templateCode, $version);
        if ($prompt === null) {
            $prompt = $this->repository->save([
                'tenant_id' => $tenantId,
                'template_code' => $templateCode,
                'feature_code' => 'general',
                'template_name' => $templateCode,
                'version' => $version,
                'system_prompt' => 'Published prompt',
                'user_prompt' => 'Published prompt',
                'status' => 'published',
                'published_at' => Carbon::now(),
            ]);
        } else {
            $prompt->status = 'published';
            $prompt->published_at = Carbon::now();
            $prompt->save();
        }

        return ['id' => (int) $prompt->id, 'status' => 'published'];
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, int $page = 1, int $pageSize = 20): array
    {
        $query = EducationAiPromptTemplate::query()->where('tenant_id', $tenantId);
        $total = (int) $query->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }
}
