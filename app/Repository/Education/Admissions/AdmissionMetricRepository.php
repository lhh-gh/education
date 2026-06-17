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

namespace App\Repository\Education\Admissions;

use App\Model\Education\Admissions\EducationAdmissionMetricDaily;
use App\Service\Education\Foundation\EducationUserContext;

final class AdmissionMetricRepository
{
    public function upsertDaily(array $data): EducationAdmissionMetricDaily
    {
        return EducationAdmissionMetricDaily::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'] ?? null,
            'metric_date' => $data['metric_date'],
            'source_id' => $data['source_id'] ?? null,
            'consultant_user_id' => $data['consultant_user_id'] ?? null,
        ], $data);
    }

    public function summary(array $filters, EducationUserContext $context): array
    {
        $query = EducationAdmissionMetricDaily::query()->where('tenant_id', $context->tenantId);
        if (isset($filters['campus_id']) && $filters['campus_id'] !== '') {
            $query->where('campus_id', (int) $filters['campus_id']);
        } elseif ($context->campusIds !== []) {
            $query->whereIn('campus_id', $context->campusIds);
        }
        if (isset($filters['start_date']) && $filters['start_date'] !== '') {
            $query->where('metric_date', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date']) && $filters['end_date'] !== '') {
            $query->where('metric_date', '<=', $filters['end_date']);
        }

        $rows = $query->get();

        return [
            'new_leads_count' => (int) $rows->sum('new_leads_count'),
            'follow_count' => (int) $rows->sum('follow_count'),
            'trial_count' => (int) $rows->sum('trial_count'),
            'trial_attended_count' => (int) $rows->sum('trial_attended_count'),
            'converted_count' => (int) $rows->sum('converted_count'),
        ];
    }
}
