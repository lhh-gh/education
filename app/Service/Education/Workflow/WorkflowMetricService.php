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

namespace App\Service\Education\Workflow;

use App\Model\Education\Workflow\EducationOperationAlert;
use App\Model\Education\Workflow\EducationWorkflowTask;

final class WorkflowMetricService
{
    /**
     * @return array{created_count: int, completed_count: int, overdue_count: int, alert_count: int}
     */
    public function dashboard(int $tenantId): array
    {
        return [
            'created_count' => (int) EducationWorkflowTask::query()->where('tenant_id', $tenantId)->count(),
            'completed_count' => (int) EducationWorkflowTask::query()->where('tenant_id', $tenantId)->where('status', 'completed')->count(),
            'overdue_count' => (int) EducationWorkflowTask::query()->where('tenant_id', $tenantId)->where('status', 'overdue')->count(),
            'alert_count' => (int) EducationOperationAlert::query()->where('tenant_id', $tenantId)->count(),
        ];
    }
}
