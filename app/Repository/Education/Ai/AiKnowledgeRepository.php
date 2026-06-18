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

use App\Model\Education\Ai\EducationAiKnowledgeDocument;

final class AiKnowledgeRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationAiKnowledgeDocument
    {
        return EducationAiKnowledgeDocument::query()->updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'document_code' => $data['document_code']],
            $data
        );
    }
}
