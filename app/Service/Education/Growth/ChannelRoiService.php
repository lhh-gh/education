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
use App\Model\Education\Growth\EducationGrowthChannelCost;
use App\Repository\Education\Growth\ChannelRoiRepository;

final class ChannelRoiService
{
    public function __construct(private readonly ChannelRoiRepository $roi) {}

    /**
     * @return array{source_id: int, lead_count: int, converted_count: int, cost_cents: int, converted_revenue_cents: int, roi: null|string}
     */
    public function aggregateDaily(int $tenantId, int $campusId, string $metricDate, int $sourceId): array
    {
        $leadIds = EducationLead::query()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('source_id', $sourceId)
            ->pluck('id')
            ->all();
        $converted = EducationLeadConversionRecord::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('lead_id', $leadIds)
            ->where('converted_at', '>=', $metricDate . ' 00:00:00')
            ->where('converted_at', '<=', $metricDate . ' 23:59:59')
            ->get();
        $revenue = $converted->sum(static fn (EducationLeadConversionRecord $record): int => (int) ($record->payload_json['converted_revenue_cents'] ?? 0));
        $cost = (int) EducationGrowthChannelCost::query()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('source_id', $sourceId)
            ->where('cost_date', $metricDate)
            ->sum('amount_cents');
        $roi = $cost > 0 ? number_format($revenue / $cost, 4, '.', '') : null;
        $row = [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'metric_date' => $metricDate,
            'source_id' => $sourceId,
            'lead_count' => \count($leadIds),
            'converted_count' => $converted->count(),
            'cost_cents' => $cost,
            'converted_revenue_cents' => $revenue,
            'roi' => $roi,
        ];
        $this->roi->saveDaily($row);

        return [
            'source_id' => $sourceId,
            'lead_count' => $row['lead_count'],
            'converted_count' => $row['converted_count'],
            'cost_cents' => $cost,
            'converted_revenue_cents' => $revenue,
            'roi' => $roi,
        ];
    }
}
