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
use App\Model\Education\Payroll\EducationTeacherSalaryBatch;
use App\Model\Enums\Education\Payroll\SalaryBatchStatus;
use App\Model\Enums\Education\Payroll\SalarySlipStatus;
use App\Repository\Education\Payroll\SalaryBatchRepository;
use App\Repository\Education\Payroll\SalarySlipRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class SalaryCalculationService
{
    public function __construct(
        private readonly SalaryBatchRepository $batchRepository,
        private readonly SalarySlipRepository $slipRepository,
        private readonly SalaryRuleService $ruleService
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{batch_id: int, status: string, teacher_count: int, total_amount_cents: int}
     */
    public function calculate(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $tenantId = (int) $context->tenantId;
            $campusId = isset($data['campus_id']) && $data['campus_id'] !== '' ? (int) $data['campus_id'] : $context->currentCampusId;
            $salaryMonth = (string) ($data['salary_month'] ?? date('Y-m'));
            $existing = $this->batchRepository->findByMonth($tenantId, $campusId, $salaryMonth);
            if ($existing instanceof EducationTeacherSalaryBatch && \in_array($existing->status, [SalaryBatchStatus::Submitted->value, SalaryBatchStatus::Approved->value, SalaryBatchStatus::Paid->value, SalaryBatchStatus::Closed->value], true)) {
                throw new BusinessException(ResultCode::CONFLICT, 'approved salary batch already exists for month', ['salary_month' => $salaryMonth]);
            }
            $batch = $existing instanceof EducationTeacherSalaryBatch ? $existing : $this->batchRepository->create([
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'batch_no' => $this->batchRepository->nextBatchNo($tenantId, $campusId),
                'salary_month' => $salaryMonth,
                'status' => SalaryBatchStatus::Draft->value,
                'source_start' => $this->sourceStart($salaryMonth),
                'source_end' => $this->sourceEnd($salaryMonth),
                'teacher_count' => 0,
                'total_amount_cents' => 0,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            $this->batchRepository->deleteDraftSlips((int) $batch->id, $tenantId);

            return $this->calculateIntoBatch($batch, $data, $context);
        });
    }

    /**
     * @return array{batch_id: int, status: string, teacher_count: int, total_amount_cents: int}
     */
    public function rebuild(int $batchId, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($batchId, $context): array {
            $batch = $this->batchRepository->lockScoped($batchId, $context);
            if (! $batch instanceof EducationTeacherSalaryBatch) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'salary batch not found in current context', ['batch_id' => $batchId]);
            }
            if ($batch->status !== SalaryBatchStatus::Draft->value && $batch->status !== SalaryBatchStatus::Calculated->value) {
                throw new BusinessException(ResultCode::CONFLICT, 'approved salary batch cannot be rebuilt', ['batch_id' => $batchId, 'status' => (string) $batch->status]);
            }
            $this->batchRepository->deleteDraftSlips((int) $batch->id, (int) $batch->tenant_id);

            return $this->calculateIntoBatch($batch, [
                'salary_month' => (string) $batch->salary_month,
                'campus_id' => $batch->campus_id,
            ], $context);
        });
    }

    /**
     * @param array<string, mixed> $data
     * @return array{batch_id: int, status: string, teacher_count: int, total_amount_cents: int}
     */
    private function calculateIntoBatch(EducationTeacherSalaryBatch $batch, array $data, EducationUserContext $context): array
    {
        $salaryMonth = (string) $batch->salary_month;
        $tenantId = (int) $batch->tenant_id;
        $campusId = $batch->campus_id === null ? null : (int) $batch->campus_id;
        $workloads = $this->workloadQuery($tenantId, $campusId, $salaryMonth, $data)->get();
        $teacherTotals = [];
        $teacherSnapshots = [];
        $itemsByTeacher = [];
        foreach ($workloads as $workload) {
            if (! $workload instanceof EducationTeacherWorkloadRecord) {
                continue;
            }
            $matched = $this->ruleService->matchForWorkload($workload, $salaryMonth, $context);
            $quantity = (float) $workload->credits;
            $amount = (int) round($quantity * $matched['unit_amount_cents']);
            $teacherId = (int) $workload->teacher_id;
            $teacherTotals[$teacherId] = ($teacherTotals[$teacherId] ?? 0) + $amount;
            $snapshot = $this->workloadSnapshot($workload);
            $teacherSnapshots[$teacherId][] = $snapshot;
            $itemsByTeacher[$teacherId][] = [
                'source_workload_id' => (int) $workload->id,
                'item_type' => 'workload',
                'item_name' => (string) $workload->workload_type,
                'quantity' => number_format($quantity, 2, '.', ''),
                'unit_amount_cents' => $matched['unit_amount_cents'],
                'amount_cents' => $amount,
                'rule_item_id' => $matched['rule_item_id'],
                'snapshot_json' => $snapshot,
            ];
        }
        foreach ($teacherTotals as $teacherId => $amount) {
            $slip = $this->slipRepository->create([
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'batch_id' => (int) $batch->id,
                'teacher_id' => $teacherId,
                'salary_month' => $salaryMonth,
                'status' => SalarySlipStatus::Draft->value,
                'workload_snapshot_json' => $teacherSnapshots[$teacherId] ?? [],
                'gross_amount_cents' => $amount,
                'adjustment_amount_cents' => 0,
                'payable_amount_cents' => $amount,
                'paid_amount_cents' => 0,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            foreach ($itemsByTeacher[$teacherId] ?? [] as $item) {
                $this->slipRepository->createItem($item + [
                    'tenant_id' => $tenantId,
                    'campus_id' => $campusId,
                    'salary_slip_id' => (int) $slip->id,
                    'teacher_id' => $teacherId,
                    'created_by' => $context->userId,
                    'updated_by' => $context->userId,
                ]);
            }
        }
        $batch->fill([
            'status' => SalaryBatchStatus::Calculated->value,
            'teacher_count' => \count($teacherTotals),
            'total_amount_cents' => array_sum($teacherTotals),
            'calculated_by' => $context->userId,
            'calculated_at' => Carbon::now()->toDateTimeString(),
            'updated_by' => $context->userId,
        ]);
        $batch->save();

        return [
            'batch_id' => (int) $batch->id,
            'status' => (string) $batch->status,
            'teacher_count' => (int) $batch->teacher_count,
            'total_amount_cents' => (int) $batch->total_amount_cents,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function workloadQuery(int $tenantId, ?int $campusId, string $salaryMonth, array $data): mixed
    {
        $query = EducationTeacherWorkloadRecord::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('recorded_at', [$this->sourceStart($salaryMonth) . ' 00:00:00', $this->sourceEnd($salaryMonth) . ' 23:59:59']);
        $campusId === null ? $query->whereNull('campus_id') : $query->where('campus_id', $campusId);
        if (\is_array($data['teacher_ids'] ?? null) && $data['teacher_ids'] !== []) {
            $query->whereIn('teacher_id', array_map('intval', $data['teacher_ids']));
        }

        return $query->orderBy('teacher_id')->orderBy('id');
    }

    private function sourceStart(string $salaryMonth): string
    {
        return Carbon::createFromFormat('Y-m-d', $salaryMonth . '-01')->startOfMonth()->toDateString();
    }

    private function sourceEnd(string $salaryMonth): string
    {
        return Carbon::createFromFormat('Y-m-d', $salaryMonth . '-01')->endOfMonth()->toDateString();
    }

    /**
     * @return array<string, mixed>
     */
    private function workloadSnapshot(EducationTeacherWorkloadRecord $workload): array
    {
        return [
            'id' => (int) $workload->id,
            'teacher_id' => (int) $workload->teacher_id,
            'lesson_id' => (int) $workload->lesson_id,
            'workload_type' => (string) $workload->workload_type,
            'lesson_type' => (string) $workload->lesson_type,
            'credits' => number_format((float) $workload->credits, 2, '.', ''),
            'student_count' => (int) $workload->student_count,
            'present_count' => (int) $workload->present_count,
            'leave_count' => (int) $workload->leave_count,
            'absent_count' => (int) $workload->absent_count,
            'recorded_at' => (string) $workload->recorded_at,
        ];
    }
}
