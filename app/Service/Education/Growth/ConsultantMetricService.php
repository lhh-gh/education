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
use App\Model\Education\Growth\EducationGrowthLeadLossRecord;
use App\Repository\Education\Growth\ConsultantMetricRepository;

final class ConsultantMetricService
{
    public function __construct(private readonly ConsultantMetricRepository $metrics) {}

    /**
     * @return array{consultant_user_id: int, assigned_leads_count: int, follow_count: int, trial_count: int, converted_count: int, lost_count: int}
     */
    public function aggregateDaily(int $tenantId, int $campusId, int $consultantUserId, string $metricDate): array
    {
        $leadIds = EducationLead::query()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('owner_user_id', $consultantUserId)
            ->pluck('id')
            ->all();
        $between = [$metricDate . ' 00:00:00', $metricDate . ' 23:59:59'];
        $row = [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'metric_date' => $metricDate,
            'consultant_user_id' => $consultantUserId,
            'assigned_leads_count' => \count($leadIds),
            'follow_count' => (int) EducationLeadFollowRecord::query()->where('tenant_id', $tenantId)->where('operator_user_id', $consultantUserId)->whereBetween('created_at', $between)->count(),
            'trial_count' => (int) EducationTrialLesson::query()->where('tenant_id', $tenantId)->where('consultant_user_id', $consultantUserId)->whereBetween('start_time', $between)->count(),
            'converted_count' => (int) EducationLeadConversionRecord::query()->where('tenant_id', $tenantId)->where('converted_by', $consultantUserId)->whereBetween('converted_at', $between)->count(),
            'lost_count' => (int) EducationGrowthLeadLossRecord::query()->where('tenant_id', $tenantId)->where('lost_by', $consultantUserId)->whereBetween('lost_at', $between)->count(),
        ];
        $this->metrics->saveDaily($row);

        return [
            'consultant_user_id' => $consultantUserId,
            'assigned_leads_count' => $row['assigned_leads_count'],
            'follow_count' => $row['follow_count'],
            'trial_count' => $row['trial_count'],
            'converted_count' => $row['converted_count'],
            'lost_count' => $row['lost_count'],
        ];
    }
}
