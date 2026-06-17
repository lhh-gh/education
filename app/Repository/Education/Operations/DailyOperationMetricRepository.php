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

namespace App\Repository\Education\Operations;

use App\Model\Education\Operations\EducationDailyOperationMetric;

final class DailyOperationMetricRepository
{
    public function upsertDailyMetric(array $data): EducationDailyOperationMetric
    {
        return EducationDailyOperationMetric::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'] ?? null,
            'metric_date' => $data['metric_date'],
        ], $data);
    }

    public function trend(int $tenantId, ?int $campusId = null): array
    {
        $query = EducationDailyOperationMetric::query()->where('tenant_id', $tenantId);
        if ($campusId !== null) {
            $query->where('campus_id', $campusId);
        }

        return $query->orderBy('metric_date')->get()->map(static fn ($row): array => $row->toArray())->all();
    }

    public function dashboardSummary(int $tenantId, ?int $campusId = null): array
    {
        $query = EducationDailyOperationMetric::query()->where('tenant_id', $tenantId);
        if ($campusId !== null) {
            $query->where('campus_id', $campusId);
        }
        $row = $query->selectRaw(
            'SUM(lessons_count) as lessons_count, SUM(consumed_credits) as consumed_credits, SUM(renewal_alert_count) as renewal_alert_count, SUM(pending_review_count) as pending_review_count'
        )->first();

        return [
            'lessons_count' => (int) ($row->lessons_count ?? 0),
            'consumed_credits' => number_format((float) ($row->consumed_credits ?? 0), 2, '.', ''),
            'renewal_alert_count' => (int) ($row->renewal_alert_count ?? 0),
            'pending_review_count' => (int) ($row->pending_review_count ?? 0),
        ];
    }
}
