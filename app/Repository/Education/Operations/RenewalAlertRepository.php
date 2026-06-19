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

use App\Model\Education\Operations\EducationRenewalAlert;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;

final class RenewalAlertRepository
{
    public function pageOpen(array $params, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($params, $context);
        foreach (['campus_id', 'student_id', 'alert_type', 'alert_level', 'status'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }
        $total = (clone $query)->count();
        $list = $query->orderByRaw("FIELD(alert_level, 'urgent', 'warning', 'normal')")
            ->orderBy('due_date')
            ->forPage((int) ($params['page'] ?? 1), (int) ($params['pageSize'] ?? 20))
            ->get()
            ->map(static fn ($row): array => $row->toArray())
            ->all();

        return ['list' => $list, 'total' => $total];
    }

    private function scopedQuery(array $params, EducationUserContext $context): mixed
    {
        $query = EducationRenewalAlert::query();
        if ($context->platformAccess) {
            if (isset($params['tenant_id']) && $params['tenant_id'] !== '') {
                $query->where('tenant_id', (int) $params['tenant_id']);
            }

            return $query;
        }

        if ($context->tenantId === null) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('tenant_id', $context->tenantId);
        if ($context->roleCode !== EducationRoleCode::TenantAdmin) {
            $query->whereIn('campus_id', $context->campusIds ?: [0]);
        }

        return $query;
    }

    public function summaryByLevel(array $params, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($params, $context)->where('status', 'open');
        if (isset($params['start_at']) && $params['start_at'] !== '') {
            $query->where('created_at', '>=', $params['start_at']);
        }
        if (isset($params['end_at']) && $params['end_at'] !== '') {
            $query->where('created_at', '<=', $params['end_at']);
        }
        $rows = $query
            ->selectRaw('alert_level, COUNT(*) as row_count')
            ->groupBy('alert_level')
            ->get()
            ->keyBy('alert_level');

        return [
            'urgent_count' => (int) ($rows->get('urgent')->row_count ?? 0),
            'warning_count' => (int) ($rows->get('warning')->row_count ?? 0),
            'normal_count' => (int) ($rows->get('normal')->row_count ?? 0),
        ];
    }

    public function findOpenAlert(int $tenantId, int $accountId, string $alertType): ?EducationRenewalAlert
    {
        return EducationRenewalAlert::query()
            ->where('tenant_id', $tenantId)
            ->where('student_course_account_id', $accountId)
            ->where('alert_type', $alertType)
            ->where('status', 'open')
            ->first();
    }

    public function createAlert(array $data): EducationRenewalAlert
    {
        return EducationRenewalAlert::query()->create($data);
    }

    public function markIgnored(EducationRenewalAlert $alert): EducationRenewalAlert
    {
        $alert->update(['status' => 'ignored']);

        return $alert->refresh();
    }

    public function markConverted(EducationRenewalAlert $alert): EducationRenewalAlert
    {
        $alert->update(['status' => 'converted']);

        return $alert->refresh();
    }

    public function markClosed(EducationRenewalAlert $alert): EducationRenewalAlert
    {
        $alert->update(['status' => 'closed']);

        return $alert->refresh();
    }
}
