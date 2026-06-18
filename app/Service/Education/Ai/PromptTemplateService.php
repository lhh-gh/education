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
        $prompt = $this->repository->save([
            'tenant_id' => $tenantId,
            'template_code' => $templateCode,
            'version' => $version,
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        return ['id' => (int) $prompt->id, 'status' => 'published'];
    }
}
