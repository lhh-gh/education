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

namespace App\Service\Education\Growth;

use App\Model\Education\Admissions\EducationLead;
use App\Model\Education\Admissions\EducationLeadConversionRecord;
use App\Model\Education\Admissions\EducationLeadFollowRecord;
use App\Model\Education\Admissions\EducationTrialLesson;
use App\Repository\Education\Growth\LeadScoreRepository;

final class LeadScoreService
{
    public function __construct(private readonly LeadScoreRepository $scores) {}

    /**
     * @return array{lead_id: int, score: int, score_level: string}
     */
    public function recalculate(int $tenantId, int $campusId, int $leadId, ?string $scoreDate = null): array
    {
        $lead = EducationLead::query()->where('tenant_id', $tenantId)->findOrFail($leadId);
        $scoreDate ??= date('Y-m-d');
        $followCount = (int) EducationLeadFollowRecord::query()->where('tenant_id', $tenantId)->where('lead_id', $leadId)->count();
        $trialCount = (int) EducationTrialLesson::query()->where('tenant_id', $tenantId)->where('lead_id', $leadId)->count();
        $convertedCount = (int) EducationLeadConversionRecord::query()->where('tenant_id', $tenantId)->where('lead_id', $leadId)->count();

        $factors = [
            ['factor_code' => 'intention', 'factor_name' => 'Intention', 'factor_value' => (string) $lead->intention_level, 'points' => $this->intentionPoints((string) $lead->intention_level), 'weight' => '1.0000'],
            ['factor_code' => 'follow_count', 'factor_name' => 'Follow count', 'factor_value' => (string) $followCount, 'points' => $followCount * 15, 'weight' => '1.0000'],
            ['factor_code' => 'trial_count', 'factor_name' => 'Trial count', 'factor_value' => (string) $trialCount, 'points' => $trialCount * 30, 'weight' => '1.0000'],
        ];
        if ($convertedCount > 0) {
            $factors[] = ['factor_code' => 'converted', 'factor_name' => 'Converted', 'factor_value' => (string) $convertedCount, 'points' => 40, 'weight' => '1.0000'];
        }

        $score = min(100, array_sum(array_column($factors, 'points')) + 10);
        $level = $this->scoreLevel((int) $score);
        $row = $this->scores->saveScore([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lead_id' => $leadId,
            'score_date' => $scoreDate,
            'score' => $score,
            'score_level' => $level,
            'stage' => (string) $lead->stage,
            'owner_user_id' => $lead->owner_user_id,
            'summary' => \sprintf('Score %d from %d follows and %d trials', $score, $followCount, $trialCount),
        ]);

        $factorRows = array_map(static fn (array $factor): array => $factor + ['campus_id' => $campusId], $factors);
        $this->scores->replaceFactors($tenantId, (int) $row->id, $factorRows);

        return ['lead_id' => $leadId, 'score' => (int) $score, 'score_level' => $level];
    }

    private function intentionPoints(string $intention): int
    {
        return match ($intention) {
            'high', 'hot' => 20,
            'medium' => 10,
            default => 0,
        };
    }

    private function scoreLevel(int $score): string
    {
        return match (true) {
            $score >= 70 => 'hot',
            $score >= 50 => 'high',
            $score >= 30 => 'medium',
            default => 'low',
        };
    }
}
