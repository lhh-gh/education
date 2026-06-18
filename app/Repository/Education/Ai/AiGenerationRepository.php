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

use App\Model\Education\Ai\EducationAiGenerationResult;
use App\Model\Education\Ai\EducationAiGenerationTask;

final class AiGenerationRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function createTask(array $data): EducationAiGenerationTask
    {
        return EducationAiGenerationTask::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createResult(array $data): EducationAiGenerationResult
    {
        return EducationAiGenerationResult::query()->create($data);
    }

    public function task(int $id): EducationAiGenerationTask
    {
        return EducationAiGenerationTask::query()->findOrFail($id);
    }

    public function result(int $id): EducationAiGenerationResult
    {
        return EducationAiGenerationResult::query()->findOrFail($id);
    }
}
