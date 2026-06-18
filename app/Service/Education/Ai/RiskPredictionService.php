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

use App\Model\Education\Ai\EducationAiRiskScore;
use App\Repository\Education\Ai\RiskPredictionRepository;

final class RiskPredictionService
{
    public function __construct(private readonly RiskPredictionRepository $repository) {}

    /**
     * @param array<string, mixed> $data
     * @return array{risk_score_id: int}
     */
    public function recordScore(array $data): array
    {
        $score = $this->repository->createScore([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'] ?? null,
            'student_id' => $data['student_id'],
            'course_account_id' => $data['course_account_id'] ?? null,
            'score_date' => $data['score_date'],
            'risk_score' => $data['risk_score'],
            'risk_level' => $data['risk_level'],
            'summary' => $data['summary'] ?? null,
            'generation_task_id' => $data['generation_task_id'] ?? null,
        ]);

        foreach ((array) ($data['factors'] ?? []) as $factor) {
            $this->repository->createFactor([
                'tenant_id' => $data['tenant_id'],
                'campus_id' => $data['campus_id'] ?? null,
                'risk_score_id' => (int) $score->id,
                'factor_code' => $factor['factor_code'],
                'factor_name' => $factor['factor_name'],
                'factor_value' => $factor['factor_value'],
                'weight' => $factor['weight'] ?? 0,
            ]);
        }

        return ['risk_score_id' => (int) $score->id];
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, int $page = 1, int $pageSize = 20): array
    {
        $query = EducationAiRiskScore::query()->where('tenant_id', $tenantId);
        $total = (int) $query->count();
        $list = $query->orderByDesc('score_date')->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }
}
