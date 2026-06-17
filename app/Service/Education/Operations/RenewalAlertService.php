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

namespace App\Service\Education\Operations;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Operations\EducationRenewalAlert;
use App\Model\Education\Operations\EducationRenewalTask;
use App\Repository\Education\Operations\RenewalAlertRepository;
use App\Repository\Education\Operations\RenewalTaskRepository;
use App\Repository\Education\Operations\StudentFollowRecordRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class RenewalAlertService
{
    public function __construct(
        private readonly RenewalAlertRepository $alertRepository,
        private readonly RenewalTaskRepository $taskRepository,
        private readonly StudentFollowRecordRepository $followRecordRepository
    ) {}

    public function scanAccounts(int $tenantId, float $lowBalanceThreshold = 2.0, int $expireSoonDays = 30): array
    {
        $created = [];
        $accounts = EducationStudentCourseAccount::query()->where('tenant_id', $tenantId)->where('status', 'active')->get();
        foreach ($accounts as $account) {
            if ((float) $account->available_units <= $lowBalanceThreshold) {
                $created[] = $this->createDedupedAlert($account, 'low_balance', (string) $account->available_units, number_format($lowBalanceThreshold, 2, '.', ''));
            }
            if ($account->expires_at !== null && Carbon::parse($account->expires_at)->lessThanOrEqualTo(Carbon::now()->addDays($expireSoonDays))) {
                $type = Carbon::parse($account->expires_at)->isPast() ? 'expired' : 'expire_soon';
                $created[] = $this->createDedupedAlert($account, $type, Carbon::parse($account->expires_at)->toDateString(), (string) $expireSoonDays);
            }
        }

        return array_values(array_filter($created));
    }

    public function assignTask(int $alertId, int $assigneeId, EducationUserContext $context, ?string $nextFollowAt = null): array
    {
        $alert = $this->alert($alertId, $context);
        $task = $this->taskRepository->assign([
            'tenant_id' => (int) $alert->tenant_id,
            'campus_id' => (int) $alert->campus_id,
            'student_id' => (int) $alert->student_id,
            'course_id' => (int) $alert->course_id,
            'renewal_alert_id' => (int) $alert->id,
            'assignee_id' => $assigneeId,
            'status' => 'pending',
            'next_follow_at' => $nextFollowAt,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return $task->toArray();
    }

    public function saveFollowUp(int $taskId, array $data, EducationUserContext $context): array
    {
        $task = EducationRenewalTask::query()->where('tenant_id', $context->tenantId)->whereKey($taskId)->first();
        if (! $task instanceof EducationRenewalTask) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'renewal task not found', ['task_id' => $taskId]);
        }
        if ($task->assignee_id !== null && (int) $task->assignee_id !== $context->userId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'renewal task is assigned to another user', ['task_id' => $taskId]);
        }
        $record = $this->followRecordRepository->createRecord([
            'tenant_id' => (int) $task->tenant_id,
            'campus_id' => (int) $task->campus_id,
            'student_id' => (int) $task->student_id,
            'renewal_task_id' => (int) $task->id,
            'follow_type' => (string) $data['follow_type'],
            'content' => (string) $data['content'],
            'next_follow_at' => $data['next_follow_at'] ?? null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
        $task = $this->taskRepository->markFollowing($task, $data['next_follow_at'] ?? null, (string) $data['content']);

        return ['follow_record_id' => (int) $record->id, 'task_status' => $task->status];
    }

    public function closeAlert(int $alertId, EducationUserContext $context): array
    {
        return $this->alertRepository->markClosed($this->alert($alertId, $context))->toArray();
    }

    private function createDedupedAlert(EducationStudentCourseAccount $account, string $type, string $triggerValue, string $thresholdValue): ?array
    {
        if ($this->alertRepository->findOpenAlert((int) $account->tenant_id, (int) $account->id, $type) instanceof EducationRenewalAlert) {
            return null;
        }

        return $this->alertRepository->createAlert([
            'tenant_id' => (int) $account->tenant_id,
            'campus_id' => (int) $account->campus_id,
            'student_id' => (int) $account->student_id,
            'course_id' => (int) $account->course_id,
            'student_course_account_id' => (int) $account->id,
            'alert_type' => $type,
            'alert_level' => $type === 'expired' || $type === 'low_balance' ? 'urgent' : 'warning',
            'status' => 'open',
            'trigger_value' => $triggerValue,
            'threshold_value' => $thresholdValue,
            'due_date' => Carbon::now()->addDays(1)->toDateString(),
        ])->toArray();
    }

    private function alert(int $id, EducationUserContext $context): EducationRenewalAlert
    {
        $alert = EducationRenewalAlert::query()->where('tenant_id', $context->tenantId)->whereKey($id)->first();
        if (! $alert instanceof EducationRenewalAlert) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'renewal alert not found', ['id' => $id]);
        }

        return $alert;
    }
}
