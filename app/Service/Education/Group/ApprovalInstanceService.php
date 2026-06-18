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

namespace App\Service\Education\Group;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Group\EducationApprovalInstance;
use App\Model\Education\Group\EducationApprovalNode;
use App\Model\Education\Group\EducationApprovalTask;
use App\Repository\Education\Group\ApprovalInstanceRepository;
use App\Repository\Education\Group\ApprovalTemplateRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class ApprovalInstanceService
{
    public function __construct(
        private readonly ApprovalInstanceRepository $repository,
        private readonly ApprovalTemplateRepository $templateRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{approval_instance_id: int, task_id: int, status: string}
     */
    public function create(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $businessType = (string) ($data['business_type'] ?? '');
            $businessId = (int) ($data['business_id'] ?? 0);
            if ($this->repository->findBusiness((int) $context->tenantId, $businessType, $businessId) instanceof EducationApprovalInstance) {
                throw new BusinessException(ResultCode::CONFLICT, 'approval instance already exists for business', ['business_type' => $businessType, 'business_id' => $businessId]);
            }
            $templateId = (int) ($data['template_id'] ?? 0);
            $node = $this->templateRepository->firstNode($templateId, $context);
            if (! $node instanceof EducationApprovalNode) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'approval node not found', ['template_id' => $templateId]);
            }
            $instance = $this->repository->createInstance([
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => $context->currentCampusId,
                'template_id' => $templateId,
                'business_type' => $businessType,
                'business_id' => $businessId,
                'status' => 'pending',
                'current_node_id' => (int) $node->id,
                'initiator_id' => $context->userId,
                'payload_json' => \is_array($data['payload_json'] ?? null) ? $data['payload_json'] : [],
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $task = $this->repository->createTask([
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => $context->currentCampusId,
                'approval_instance_id' => (int) $instance->id,
                'node_id' => (int) $node->id,
                'assignee_user_id' => $this->assigneeUserId($node),
                'status' => 'pending',
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return ['approval_instance_id' => (int) $instance->id, 'task_id' => (int) $task->id, 'status' => 'pending'];
        });
    }

    /**
     * @param array<string, mixed> $data
     * @return array{task_id: int, task_status: string, instance_status: string}
     */
    public function completeTask(int $taskId, array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($taskId, $data, $context): array {
            $task = $this->repository->lockTask($taskId, $context);
            if (! $task instanceof EducationApprovalTask) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'approval task not found', ['task_id' => $taskId]);
            }
            if ((int) $task->assignee_user_id !== $context->userId && ($data['override_permission'] ?? false) !== true) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'approval task is assigned to another user', ['task_id' => $taskId]);
            }
            $result = (string) ($data['result'] ?? '');
            if (! \in_array($result, ['approved', 'rejected'], true)) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'result has an invalid value', ['field' => 'result']);
            }
            $instance = $this->repository->findInstance((int) $task->approval_instance_id, $context);
            if (! $instance instanceof EducationApprovalInstance) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'approval instance not found', ['approval_instance_id' => (int) $task->approval_instance_id]);
            }
            $beforeStatus = (string) $instance->status;
            $afterStatus = $result === 'approved' ? 'approved' : 'rejected';
            $now = Carbon::now()->toDateTimeString();
            $task->fill(['status' => 'completed', 'result' => $result, 'comment' => $data['comment'] ?? null, 'completed_at' => $now, 'updated_by' => $context->userId]);
            $task->save();
            $instance->fill(['status' => $afterStatus, 'completed_at' => $now, 'updated_by' => $context->userId]);
            $instance->save();
            $this->repository->createLog([
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => $task->campus_id,
                'approval_instance_id' => (int) $instance->id,
                'task_id' => (int) $task->id,
                'operator_id' => $context->userId,
                'action' => $result,
                'before_status' => $beforeStatus,
                'after_status' => $afterStatus,
                'comment' => $data['comment'] ?? null,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return ['task_id' => (int) $task->id, 'task_status' => (string) $task->status, 'instance_status' => (string) $instance->status];
        });
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageTasks(array $filters, EducationUserContext $context): array
    {
        return $this->repository->pageTasks($filters, $context);
    }

    private function assigneeUserId(EducationApprovalNode $node): int
    {
        $value = \is_array($node->assignee_value_json) ? $node->assignee_value_json : [];

        return (int) ($value['user_ids'][0] ?? 0);
    }
}
