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

namespace App\Repository\Education\Group;

use App\Model\Education\Group\EducationApprovalInstance;
use App\Model\Education\Group\EducationApprovalLog;
use App\Model\Education\Group\EducationApprovalTask;
use App\Service\Education\Foundation\EducationUserContext;

final class ApprovalInstanceRepository
{
    public function findBusiness(int $tenantId, string $businessType, int $businessId): ?EducationApprovalInstance
    {
        $row = EducationApprovalInstance::query()->where('tenant_id', $tenantId)->where('business_type', $businessType)->where('business_id', $businessId)->first();

        return $row instanceof EducationApprovalInstance ? $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createInstance(array $data): EducationApprovalInstance
    {
        return EducationApprovalInstance::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createTask(array $data): EducationApprovalTask
    {
        return EducationApprovalTask::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createLog(array $data): EducationApprovalLog
    {
        return EducationApprovalLog::query()->create($data);
    }

    public function lockTask(int $taskId, EducationUserContext $context): ?EducationApprovalTask
    {
        $row = EducationApprovalTask::query()->where('tenant_id', $context->tenantId)->whereKey($taskId)->lockForUpdate()->first();

        return $row instanceof EducationApprovalTask ? $row : null;
    }

    public function findInstance(int $id, EducationUserContext $context): ?EducationApprovalInstance
    {
        $row = EducationApprovalInstance::query()->where('tenant_id', $context->tenantId)->whereKey($id)->first();

        return $row instanceof EducationApprovalInstance ? $row : null;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageTasks(array $filters, EducationUserContext $context): array
    {
        $query = EducationApprovalTask::query()->where('tenant_id', $context->tenantId);
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }
        $total = (clone $query)->count();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationApprovalTask $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
