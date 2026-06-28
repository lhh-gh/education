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

namespace App\Repository\Education\Workflow;

use App\Model\Education\Workflow\EducationWorkflowTemplate;

final class WorkflowTemplateRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationWorkflowTemplate
    {
        return EducationWorkflowTemplate::query()->create($data);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $query = EducationWorkflowTemplate::query()->where('tenant_id', $tenantId);
        if (($filters['task_type'] ?? '') !== '') {
            $query->where('task_type', $filters['task_type']);
        }
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }
        $total = (int) $query->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }
}
