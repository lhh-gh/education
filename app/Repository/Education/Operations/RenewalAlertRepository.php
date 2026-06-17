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

final class RenewalAlertRepository
{
    public function pageOpen(array $params, int $tenantId): array
    {
        $query = EducationRenewalAlert::query()->where('tenant_id', $tenantId);
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
