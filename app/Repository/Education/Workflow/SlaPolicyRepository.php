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

use App\Model\Education\Workflow\EducationWorkflowSlaPolicy;

final class SlaPolicyRepository
{
    public function enabledForTaskType(int $tenantId, string $taskType): ?EducationWorkflowSlaPolicy
    {
        return EducationWorkflowSlaPolicy::query()
            ->where('tenant_id', $tenantId)
            ->where('task_type', $taskType)
            ->where('status', 'enabled')
            ->first();
    }
}
