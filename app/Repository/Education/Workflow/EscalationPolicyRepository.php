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

use App\Model\Education\Workflow\EducationWorkflowEscalationPolicy;
use Hyperf\Database\Model\Collection;

final class EscalationPolicyRepository
{
    /**
     * @return Collection<int, EducationWorkflowEscalationPolicy>
     */
    public function enabledForTaskType(int $tenantId, string $taskType): Collection
    {
        return EducationWorkflowEscalationPolicy::query()
            ->where('tenant_id', $tenantId)
            ->where('task_type', $taskType)
            ->where('status', 'enabled')
            ->get();
    }
}
