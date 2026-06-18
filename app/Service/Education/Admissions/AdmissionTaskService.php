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

namespace App\Service\Education\Admissions;

use App\Repository\Education\Admissions\AdmissionTaskRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class AdmissionTaskService
{
    public function __construct(private readonly AdmissionTaskRepository $repository) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }

    public function create(array $data, EducationUserContext $context): array
    {
        return $this->repository->create([
            'tenant_id' => $context->tenantId,
            'campus_id' => $data['campus_id'] ?? $context->currentCampusId,
            'lead_id' => $data['lead_id'] ?? null,
            'task_type' => $data['task_type'] ?? 'follow',
            'title' => trim((string) $data['title']),
            'assignee_user_id' => (int) $data['assignee_user_id'],
            'status' => 'pending',
            'due_at' => $data['due_at'] ?? null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ])->toArray();
    }
}
