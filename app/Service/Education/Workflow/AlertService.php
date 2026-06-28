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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Workflow\EducationOperationAlert;
use App\Repository\Education\Workflow\OperationAlertRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class AlertService
{
    public function __construct(
        private readonly OperationAlertRepository $alertRepository,
        private readonly WorkflowTaskService $taskService
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{alert_id: int, status: string}
     */
    public function createAlert(array $data): array
    {
        $tenantId = (int) $data['tenant_id'];
        $dedupeKey = (string) ($data['dedupe_key'] ?? '');
        if ($dedupeKey !== '') {
            $existing = $this->alertRepository->activeByDedupeKey($tenantId, $dedupeKey);
            if ($existing !== null) {
                return ['alert_id' => (int) $existing->id, 'status' => (string) $existing->status->value];
            }
        }

        $alert = $this->alertRepository->create([
            'tenant_id' => $tenantId,
            'campus_id' => $data['campus_id'] ?? null,
            'alert_no' => $data['alert_no'] ?? uniqid('AL', true),
            'alert_type' => (string) $data['alert_type'],
            'level' => $data['level'] ?? 'warning',
            'status' => 'open',
            'title' => (string) $data['title'],
            'content' => (string) $data['content'],
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'dedupe_key' => $data['dedupe_key'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ]);

        return ['alert_id' => (int) $alert->id, 'status' => 'open'];
    }

    /**
     * @return array{alert_id: int, converted_task_id: int, status: string}
     */
    public function convertToTask(int $alertId, int $assigneeUserId, ?string $dueAt, EducationUserContext $context): array
    {
        $alert = $this->alertInContext($alertId, $context);
        if ($alert->converted_task_id !== null) {
            return ['alert_id' => $alertId, 'converted_task_id' => (int) $alert->converted_task_id, 'status' => 'converted'];
        }

        $task = $this->taskService->createTask([
            'tenant_id' => (int) $alert->tenant_id,
            'campus_id' => $alert->campus_id,
            'task_type' => (string) $alert->alert_type,
            'title' => (string) $alert->title,
            'source_type' => $alert->source_type,
            'source_id' => $alert->source_id,
            'dedupe_key' => $alert->dedupe_key,
            'due_at' => $dueAt,
            'assignee_user_id' => $assigneeUserId,
        ]);
        $alert->status = 'converted';
        $alert->converted_task_id = $task['task_id'];
        $alert->updated_by = $context->userId;
        $alert->save();

        return ['alert_id' => $alertId, 'converted_task_id' => $task['task_id'], 'status' => 'converted'];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(EducationUserContext $context, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationOperationAlert::query(), $filters, $context);
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }
        $total = (int) (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * @return array{alert_id: int, status: string}
     */
    public function setStatus(int $alertId, string $status, EducationUserContext $context): array
    {
        $alert = $this->alertInContext($alertId, $context);
        $alert->status = $status;
        $alert->updated_by = $context->userId;
        $alert->save();

        return ['alert_id' => $alertId, 'status' => $status];
    }

    private function alertInContext(int $alertId, EducationUserContext $context): EducationOperationAlert
    {
        $alert = (new EducationScopeQuery())->applyTenantCampus(
            EducationOperationAlert::query()->whereKey($alertId),
            [],
            $context
        )->first();
        if (! $alert instanceof EducationOperationAlert) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'workflow alert not found in current context', ['alert_id' => $alertId]);
        }

        return $alert;
    }
}
