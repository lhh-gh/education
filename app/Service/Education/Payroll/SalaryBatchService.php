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
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class SalaryBatchService
{
    public function __construct(
        private readonly SalaryBatchRepository $repository
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
     * @return array{batch_id: int, status: string}
     */
    public function submitReview(int $batchId, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($batchId, $context): array {
            $batch = $this->repository->lockScoped($batchId, $context);
            if (! $batch instanceof EducationTeacherSalaryBatch) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'salary batch not found in current context', ['batch_id' => $batchId]);
            }
            if ($batch->status !== SalaryBatchStatus::Calculated->value) {
                throw new BusinessException(ResultCode::CONFLICT, 'salary batch is not calculated', ['batch_id' => $batchId, 'status' => (string) $batch->status]);
            }
            $now = Carbon::now()->toDateTimeString();
            $batch->fill([
                'status' => SalaryBatchStatus::Submitted->value,
                'submitted_at' => $now,
                'updated_by' => $context->userId,
            ]);
            $batch->save();
            EducationTeacherSalarySlip::query()
                ->where('tenant_id', $batch->tenant_id)
                ->where('batch_id', $batch->id)
                ->update(['status' => SalarySlipStatus::PendingReview->value, 'updated_by' => $context->userId]);

            return ['batch_id' => (int) $batch->id, 'status' => (string) $batch->status];
        });
    }
}
