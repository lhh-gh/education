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
use App\Repository\Education\Growth\ConversionFunnelRepository;

final class ConversionFunnelService
{
    public function __construct(private readonly ConversionFunnelRepository $funnels) {}

    /**
     * @return array{stage: string, lead_count: int, next_stage_count: int, conversion_rate: null|string}
     */
    public function aggregateStage(int $tenantId, ?int $campusId, string $metricDate, ?int $sourceId, string $stage, string $nextStage): array
    {
        $query = EducationLead::query()->where('tenant_id', $tenantId)->where('stage', $stage);
        $nextQuery = EducationLead::query()->where('tenant_id', $tenantId)->where('stage', $nextStage);
        if ($campusId !== null) {
            $query->where('campus_id', $campusId);
            $nextQuery->where('campus_id', $campusId);
        }
        if ($sourceId !== null) {
            $query->where('source_id', $sourceId);
            $nextQuery->where('source_id', $sourceId);
        }
        $leadCount = (int) $query->count();
        $nextCount = (int) $nextQuery->count();
        $rate = $leadCount > 0 ? number_format($nextCount / $leadCount, 4, '.', '') : null;
        $this->funnels->saveDaily([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'metric_date' => $metricDate,
            'source_id' => $sourceId,
            'stage' => $stage,
            'lead_count' => $leadCount,
            'next_stage_count' => $nextCount,
            'conversion_rate' => $rate,
        ]);

        return ['stage' => $stage, 'lead_count' => $leadCount, 'next_stage_count' => $nextCount, 'conversion_rate' => $rate];
    }
}
