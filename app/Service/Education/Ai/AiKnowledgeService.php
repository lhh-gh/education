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

use App\Repository\Education\Ai\AiKnowledgeRepository;

final class AiKnowledgeService
{
    public function __construct(private readonly AiKnowledgeRepository $repository) {}

    /**
     * @param array<string, mixed> $data
     * @return array{id: int, document_code: string}
     */
    public function save(array $data): array
    {
        $document = $this->repository->save($data);

        return ['id' => (int) $document->id, 'document_code' => (string) $document->document_code];
    }
}
