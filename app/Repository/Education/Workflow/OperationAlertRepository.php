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

use App\Model\Education\Workflow\EducationOperationAlert;
use App\Model\Education\Workflow\EducationOperationAlertLog;

final class OperationAlertRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationOperationAlert
    {
        return EducationOperationAlert::query()->create($data);
    }

    public function activeByDedupeKey(int $tenantId, string $dedupeKey): ?EducationOperationAlert
    {
        return EducationOperationAlert::query()
            ->where('tenant_id', $tenantId)
            ->where('dedupe_key', $dedupeKey)
            ->where('status', 'open')
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createLog(array $data): EducationOperationAlertLog
    {
        return EducationOperationAlertLog::query()->create($data);
    }
}
