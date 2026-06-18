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

use App\Model\Education\Ai\EducationAiReviewLog;

final class AiReviewRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationAiReviewLog
    {
        return EducationAiReviewLog::query()->create($data);
    }
}
