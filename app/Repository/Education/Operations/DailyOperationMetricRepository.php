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
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;

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

    public function trend(EducationUserContext $context, ?int $campusId = null): array
    {
        $query = $this->scopedQuery($context, $campusId);

        return $query->orderBy('metric_date')->get()->map(static fn ($row): array => $row->toArray())->all();
    }

    public function dashboardSummary(EducationUserContext $context, ?int $campusId = null): array
    {
        $query = $this->scopedQuery($context, $campusId);
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

    private function scopedQuery(EducationUserContext $context, ?int $campusId = null): mixed
    {
        $query = EducationDailyOperationMetric::query();
        if (! $context->platformAccess) {
            if ($context->tenantId === null) {
                return $query->whereRaw('1 = 0');
            }
            $query->where('tenant_id', $context->tenantId);
        }
        if ($campusId !== null) {
            return $query->where('campus_id', $campusId);
        }
        if (! $context->platformAccess && $context->roleCode !== EducationRoleCode::TenantAdmin) {
            $query->whereIn('campus_id', $context->campusIds ?: [0]);
        }

        return $query;
    }
}
