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

use App\Model\Education\Operations\EducationRenewalTask;

final class RenewalTaskRepository
{
    public function pageMineOrCampus(array $params, int $tenantId, ?int $assigneeId = null): array
    {
        $query = EducationRenewalTask::query()->where('tenant_id', $tenantId);
        if ($assigneeId !== null) {
            $query->where('assignee_id', $assigneeId);
        }
        foreach (['campus_id', 'student_id', 'status'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }
        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage((int) ($params['page'] ?? 1), (int) ($params['pageSize'] ?? 20))->get()->map(static fn ($row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }

    public function assign(array $data): EducationRenewalTask
    {
        return EducationRenewalTask::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'renewal_alert_id' => $data['renewal_alert_id'],
        ], $data);
    }

    public function markFollowing(EducationRenewalTask $task, ?string $nextFollowAt = null, ?string $result = null): EducationRenewalTask
    {
        $task->update(['status' => 'following', 'next_follow_at' => $nextFollowAt, 'result' => $result]);

        return $task->refresh();
    }

    public function markDone(EducationRenewalTask $task, ?string $result = null): EducationRenewalTask
    {
        $task->update(['status' => 'done', 'result' => $result]);

        return $task->refresh();
    }
}
