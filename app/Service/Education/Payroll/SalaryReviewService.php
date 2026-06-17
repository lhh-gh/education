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
use App\Model\Education\Payroll\EducationTeacherSalaryBatch;
use App\Model\Education\Payroll\EducationTeacherSalarySlip;
use App\Model\Enums\Education\Payroll\SalaryBatchStatus;
use App\Model\Enums\Education\Payroll\SalarySlipStatus;
use App\Repository\Education\Payroll\SalaryBatchRepository;
use App\Repository\Education\Payroll\SalaryReviewRepository;
use App\Repository\Education\Payroll\SalarySlipRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class SalaryReviewService
{
    public function __construct(
        private readonly SalaryBatchRepository $batchRepository,
        private readonly SalarySlipRepository $slipRepository,
        private readonly SalaryReviewRepository $reviewRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{batch_id: int, status: string}
     */
    public function approveBatch(int $batchId, array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($batchId, $data, $context): array {
            $batch = $this->batchRepository->lockScoped($batchId, $context);
            if (! $batch instanceof EducationTeacherSalaryBatch) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'salary batch not found in current context', ['batch_id' => $batchId]);
            }
            if (! \in_array($batch->status, [SalaryBatchStatus::Calculated->value, SalaryBatchStatus::Submitted->value], true)) {
                throw new BusinessException(ResultCode::CONFLICT, 'salary batch is not submitted', ['batch_id' => $batchId, 'status' => (string) $batch->status]);
            }
            $now = Carbon::now()->toDateTimeString();
            $batch->fill([
                'status' => SalaryBatchStatus::Approved->value,
                'approved_at' => $now,
                'updated_by' => $context->userId,
            ]);
            $batch->save();
            EducationTeacherSalarySlip::query()
                ->where('tenant_id', $batch->tenant_id)
                ->where('batch_id', $batch->id)
                ->update(['status' => SalarySlipStatus::Approved->value, 'approved_at' => $now, 'updated_by' => $context->userId]);
            $this->reviewRepository->createReview([
                'tenant_id' => (int) $batch->tenant_id,
                'campus_id' => $batch->campus_id === null ? null : (int) $batch->campus_id,
                'batch_id' => (int) $batch->id,
                'salary_slip_id' => null,
                'review_level' => 1,
                'reviewer_id' => $context->userId,
                'status' => SalaryBatchStatus::Approved->value,
                'review_note' => $data['review_note'] ?? null,
                'reviewed_at' => $now,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return ['batch_id' => (int) $batch->id, 'status' => (string) $batch->status];
        });
    }

    /**
     * @param array<string, mixed> $data
     * @return array{batch_id: int, status: string}
     */
    public function rejectBatch(int $batchId, array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($batchId, $data, $context): array {
            $batch = $this->batchRepository->lockScoped($batchId, $context);
            if (! $batch instanceof EducationTeacherSalaryBatch) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'salary batch not found in current context', ['batch_id' => $batchId]);
            }
            $batch->fill(['status' => SalaryBatchStatus::Rejected->value, 'updated_by' => $context->userId]);
            $batch->save();
            $this->reviewRepository->createReview([
                'tenant_id' => (int) $batch->tenant_id,
                'campus_id' => $batch->campus_id === null ? null : (int) $batch->campus_id,
                'batch_id' => (int) $batch->id,
                'salary_slip_id' => null,
                'review_level' => 1,
                'reviewer_id' => $context->userId,
                'status' => SalaryBatchStatus::Rejected->value,
                'review_note' => $data['review_note'] ?? null,
                'reviewed_at' => Carbon::now()->toDateTimeString(),
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return ['batch_id' => (int) $batch->id, 'status' => (string) $batch->status];
        });
    }

    /**
     * @param array<string, mixed> $data
     * @return array{adjustment_id: int, payable_amount_cents: int}
     */
    public function adjustSlip(int $salarySlipId, array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($salarySlipId, $data, $context): array {
            $slip = $this->slipRepository->lockScoped($salarySlipId, $context);
            if (! $slip instanceof EducationTeacherSalarySlip) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'salary slip not found in current context', ['salary_slip_id' => $salarySlipId]);
            }
            if ($slip->status === SalarySlipStatus::Paid->value) {
                throw new BusinessException(ResultCode::CONFLICT, 'paid salary slip cannot be adjusted', ['salary_slip_id' => $salarySlipId]);
            }
            $amount = (int) ($data['amount_cents'] ?? 0);
            if ($amount === 0) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'amount_cents must not be zero', ['field' => 'amount_cents']);
            }
            $adjustment = $this->reviewRepository->createAdjustment([
                'tenant_id' => (int) $slip->tenant_id,
                'campus_id' => $slip->campus_id === null ? null : (int) $slip->campus_id,
                'salary_slip_id' => (int) $slip->id,
                'teacher_id' => (int) $slip->teacher_id,
                'adjustment_type' => (string) ($data['adjustment_type'] ?? 'adjustment'),
                'amount_cents' => $amount,
                'reason' => (string) ($data['reason'] ?? ''),
                'operator_id' => $context->userId,
                'approved_by' => $context->userId,
                'approved_at' => Carbon::now()->toDateTimeString(),
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $this->slipRepository->createItem([
                'tenant_id' => (int) $slip->tenant_id,
                'campus_id' => $slip->campus_id === null ? null : (int) $slip->campus_id,
                'salary_slip_id' => (int) $slip->id,
                'teacher_id' => (int) $slip->teacher_id,
                'source_workload_id' => null,
                'item_type' => 'adjustment',
                'item_name' => (string) ($data['adjustment_type'] ?? 'adjustment'),
                'quantity' => '1.00',
                'unit_amount_cents' => abs($amount),
                'amount_cents' => $amount,
                'rule_item_id' => null,
                'snapshot_json' => ['reason' => (string) ($data['reason'] ?? ''), 'adjustment_id' => (int) $adjustment->id],
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $newAdjustment = (int) $slip->adjustment_amount_cents + $amount;
            $newPayable = (int) $slip->gross_amount_cents + $newAdjustment;
            $slip->fill([
                'adjustment_amount_cents' => $newAdjustment,
                'payable_amount_cents' => max(0, $newPayable),
                'updated_by' => $context->userId,
            ]);
            $slip->save();

            return ['adjustment_id' => (int) $adjustment->id, 'payable_amount_cents' => (int) $slip->payable_amount_cents];
        });
    }
}
