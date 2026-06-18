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

use App\Model\Education\Ai\EducationAiRiskFactor;
use App\Model\Education\Ai\EducationAiRiskScore;

final class RiskPredictionRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function createScore(array $data): EducationAiRiskScore
    {
        return EducationAiRiskScore::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFactor(array $data): EducationAiRiskFactor
    {
        return EducationAiRiskFactor::query()->create($data);
    }
}
