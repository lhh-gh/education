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
use App\Model\Education\Payroll\EducationTeacherSalarySlip;
use App\Model\Enums\Education\Payroll\SalarySlipStatus;
use App\Repository\Education\Payroll\SalaryPaymentRepository;
use App\Repository\Education\Payroll\SalarySlipRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;

final class SalaryPaymentService
{
    public function __construct(
        private readonly SalarySlipRepository $slipRepository,
        private readonly SalaryPaymentRepository $paymentRepository
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->paymentRepository->page($filters, $context);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{payment_id: int, slip_status: string}
     */
    public function mark(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $slip = $this->slipRepository->lockScoped((int) ($data['salary_slip_id'] ?? 0), $context);
            if (! $slip instanceof EducationTeacherSalarySlip) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'salary slip not found in current context', ['salary_slip_id' => (int) ($data['salary_slip_id'] ?? 0)]);
            }
            if ($slip->status !== SalarySlipStatus::Approved->value) {
                throw new BusinessException(ResultCode::CONFLICT, 'salary slip is not approved', ['salary_slip_id' => (int) $slip->id]);
            }
            $payment = $this->paymentRepository->create([
                'tenant_id' => (int) $slip->tenant_id,
                'campus_id' => $slip->campus_id === null ? null : (int) $slip->campus_id,
                'salary_slip_id' => (int) $slip->id,
                'teacher_id' => (int) $slip->teacher_id,
                'payment_no' => (string) ($data['payment_no'] ?? ''),
                'paid_amount_cents' => (int) ($data['paid_amount_cents'] ?? $slip->payable_amount_cents),
                'payment_method' => (string) ($data['payment_method'] ?? 'manual'),
                'paid_at' => (string) ($data['paid_at'] ?? date('Y-m-d H:i:s')),
                'operator_id' => $context->userId,
                'remark' => $data['remark'] ?? null,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $slip->fill([
                'paid_amount_cents' => (int) $payment->paid_amount_cents,
                'paid_at' => $payment->paid_at,
                'status' => SalarySlipStatus::Paid->value,
                'updated_by' => $context->userId,
            ]);
            $slip->save();

            return ['payment_id' => (int) $payment->id, 'slip_status' => (string) $slip->status];
        });
    }
}
