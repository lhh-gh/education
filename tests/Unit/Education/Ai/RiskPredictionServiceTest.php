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

namespace HyperfTests\Unit\Education\Ai;

use App\Model\Education\Ai\EducationAiRiskFactor;
use App\Model\Education\Ai\EducationAiRiskScore;
use App\Service\Education\Ai\RiskPredictionService;

/**
 * @internal
 * @coversNothing
 */
final class RiskPredictionServiceTest extends AiTestCase
{
    public function testRiskScoreAndFactorsArePersisted(): void
    {
        $fixture = $this->aiFixture('ai_risk');
        $service = make(RiskPredictionService::class);

        $result = $service->recordScore([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'student_id' => $fixture['student_id'],
            'score_date' => '2026-06-18',
            'risk_score' => 72,
            'risk_level' => 'warning',
            'factors' => [
                ['factor_code' => 'low_balance', 'factor_name' => 'Low balance', 'factor_value' => '2 lessons', 'weight' => '0.7000'],
            ],
        ]);

        self::assertTrue(EducationAiRiskScore::query()->where('id', $result['risk_score_id'])->exists());
        self::assertTrue(EducationAiRiskFactor::query()->where('risk_score_id', $result['risk_score_id'])->where('factor_code', 'low_balance')->exists());
    }
}
