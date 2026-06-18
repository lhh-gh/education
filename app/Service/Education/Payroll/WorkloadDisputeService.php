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

namespace App\Service\Education\Payroll;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Operations\EducationTeacherWorkloadRecord;
use App\Model\Education\Payroll\EducationTeacherWorkloadDispute;
use App\Model\Enums\Education\Payroll\DisputeStatus;
use App\Repository\Education\Payroll\WorkloadDisputeRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class WorkloadDisputeService
{
    public function __construct(
        private readonly WorkloadDisputeRepository $repository
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{dispute_id: int, status: string}
     */
    public function submitForTeacher(int $teacherId, array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($teacherId, $data, $context): array {
            $workload = EducationTeacherWorkloadRecord::query()
                ->where('tenant_id', $context->tenantId)
                ->whereKey((int) ($data['source_workload_id'] ?? 0))
                ->first();
            if (! $workload instanceof EducationTeacherWorkloadRecord) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'workload not found in current context', ['source_workload_id' => (int) ($data['source_workload_id'] ?? 0)]);
            }
            if ((int) $workload->teacher_id !== $teacherId) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'workload does not belong to current teacher', ['source_workload_id' => (int) $workload->id]);
            }
            $dispute = $this->repository->create([
                'tenant_id' => (int) $workload->tenant_id,
                'campus_id' => $workload->campus_id === null ? null : (int) $workload->campus_id,
                'teacher_id' => $teacherId,
                'source_workload_id' => (int) $workload->id,
                'salary_slip_id' => isset($data['salary_slip_id']) && $data['salary_slip_id'] !== '' ? (int) $data['salary_slip_id'] : null,
                'dispute_type' => (string) ($data['dispute_type'] ?? 'workload'),
                'content' => (string) ($data['content'] ?? ''),
                'status' => DisputeStatus::Pending->value,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return ['dispute_id' => (int) $dispute->id, 'status' => (string) $dispute->status];
        });
    }

    /**
     * @param array<string, mixed> $data
     * @return array{dispute_id: int, status: string}
     */
    public function review(int $disputeId, array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($disputeId, $data, $context): array {
            $dispute = $this->repository->lockScoped($disputeId, $context);
            if (! $dispute instanceof EducationTeacherWorkloadDispute) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'workload dispute not found in current context', ['dispute_id' => $disputeId]);
            }
            if ($dispute->status !== DisputeStatus::Pending->value) {
                throw new BusinessException(ResultCode::CONFLICT, 'workload dispute is not pending');
            }
            $status = (string) ($data['status'] ?? DisputeStatus::Rejected->value);
            if (! \in_array($status, [DisputeStatus::Approved->value, DisputeStatus::Rejected->value], true)) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'dispute review status is invalid', ['field' => 'status']);
            }
            $dispute->fill([
                'status' => $status,
                'reviewed_by' => $context->userId,
                'reviewed_at' => Carbon::now()->toDateTimeString(),
                'review_note' => $data['review_note'] ?? null,
                'updated_by' => $context->userId,
            ]);
            $dispute->save();

            return ['dispute_id' => (int) $dispute->id, 'status' => (string) $dispute->status];
        });
    }
}
