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

use App\Model\Education\Admissions\EducationAdmissionTask;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class AdmissionTaskRepository
{
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationAdmissionTask::query(), $filters, $context);
        foreach (['lead_id', 'task_type', 'assignee_user_id', 'status'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        return $this->paginate($query->orderBy('due_at')->orderByDesc('id'), $filters);
    }

    public function create(array $data): EducationAdmissionTask
    {
        return EducationAdmissionTask::query()->create($data);
    }

    public function completeByLead(int $tenantId, int $leadId, int $operatorId): void
    {
        EducationAdmissionTask::query()
            ->where('tenant_id', $tenantId)
            ->where('lead_id', $leadId)
            ->whereIn('status', ['pending', 'processing', 'overdue'])
            ->update([
                'status' => 'done',
                'completed_at' => date('Y-m-d H:i:s'),
                'result' => 'converted',
                'updated_by' => $operatorId,
            ]);
    }

    private function paginate(mixed $query, array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->forPage($page, $pageSize)->get()->map(static fn ($row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
