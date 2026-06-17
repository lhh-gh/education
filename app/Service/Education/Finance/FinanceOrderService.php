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

namespace App\Service\Education\Finance;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Finance\EducationFinanceOrder;
use App\Model\Enums\Education\Finance\FinanceOrderStatus;
use App\Repository\Education\Academic\EnrollmentRepository;
use App\Repository\Education\Finance\FinanceOrderRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;

final class FinanceOrderService
{
    public function __construct(
        private readonly FinanceOrderRepository $repository,
        private readonly EnrollmentRepository $enrollmentRepository
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
     * @return array{order_id: int, order_no: string, enrollment_id: int, status: string, total_amount_cents: int}
     */
    public function createFromEnrollment(int $enrollmentId, array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($enrollmentId, $data, $context): array {
            $enrollment = $this->enrollmentRepository->lockScoped($enrollmentId, $context);
            if (! $enrollment instanceof EducationEnrollment) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'enrollment not found in current context', ['enrollment_id' => $enrollmentId]);
            }
            if ($this->repository->findByEnrollment((int) $enrollment->tenant_id, $enrollmentId) instanceof EducationFinanceOrder) {
                throw new BusinessException(ResultCode::CONFLICT, 'finance order already exists for enrollment', ['enrollment_id' => $enrollmentId]);
            }

            $items = $this->items($data['items'] ?? []);
            $totalAmount = array_sum(array_column($items, 'total_amount_cents'));
            $order = $this->repository->create([
                'tenant_id' => (int) $enrollment->tenant_id,
                'campus_id' => (int) $enrollment->campus_id,
                'order_no' => $this->repository->nextOrderNo((int) $enrollment->tenant_id, (int) $enrollment->campus_id),
                'order_type' => (string) ($data['order_type'] ?? 'enrollment'),
                'student_id' => (int) ($data['student_id'] ?? $enrollment->student_id),
                'guardian_id' => isset($data['guardian_id']) && $data['guardian_id'] !== '' ? (int) $data['guardian_id'] : null,
                'enrollment_id' => $enrollmentId,
                'student_course_account_id' => $enrollment->account_id === null ? null : (int) $enrollment->account_id,
                'total_amount_cents' => $totalAmount,
                'paid_amount_cents' => 0,
                'refund_amount_cents' => 0,
                'discount_amount_cents' => (int) ($data['discount_amount_cents'] ?? 0),
                'status' => FinanceOrderStatus::Pending->value,
                'due_at' => $data['due_at'] ?? null,
                'remark' => $data['remark'] ?? null,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            foreach ($items as $item) {
                $this->repository->createItem([
                    'tenant_id' => (int) $order->tenant_id,
                    'campus_id' => (int) $order->campus_id,
                    'order_id' => (int) $order->id,
                    'item_type' => $item['item_type'],
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit_amount_cents' => $item['unit_amount_cents'],
                    'total_amount_cents' => $item['total_amount_cents'],
                    'source_type' => $item['source_type'],
                    'source_id' => $item['source_id'],
                    'created_by' => $context->userId,
                    'updated_by' => $context->userId,
                ]);
            }

            return $this->summary($order->refresh());
        });
    }

    public function detail(int $id, EducationUserContext $context): EducationFinanceOrder
    {
        $order = $this->repository->findScoped($id, $context);
        if (! $order instanceof EducationFinanceOrder) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'finance order not found in current context', ['order_id' => $id]);
        }

        return $order;
    }

    /**
     * @return array{order_id: int, order_no: string, enrollment_id: int, status: string, total_amount_cents: int}
     */
    private function summary(EducationFinanceOrder $order): array
    {
        return [
            'order_id' => (int) $order->id,
            'order_no' => (string) $order->order_no,
            'enrollment_id' => (int) $order->enrollment_id,
            'status' => (string) $order->status,
            'total_amount_cents' => (int) $order->total_amount_cents,
        ];
    }

    /**
     * @return array<int, array{item_type: string, item_name: string, quantity: string, unit_amount_cents: int, total_amount_cents: int, source_type: ?string, source_id: ?int}>
     */
    private function items(mixed $items): array
    {
        if (! \is_array($items) || $items === []) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'finance order items are required');
        }
        $normalized = [];
        foreach ($items as $item) {
            if (! \is_array($item)) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'finance order item is invalid');
            }
            $quantity = number_format(round((float) ($item['quantity'] ?? 1), 2), 2, '.', '');
            $unitAmount = (int) ($item['unit_amount_cents'] ?? 0);
            if ($unitAmount <= 0 || (float) $quantity <= 0) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'finance order item amount is invalid');
            }
            $normalized[] = [
                'item_type' => (string) ($item['item_type'] ?? 'manual'),
                'item_name' => (string) ($item['item_name'] ?? 'Finance item'),
                'quantity' => $quantity,
                'unit_amount_cents' => $unitAmount,
                'total_amount_cents' => (int) round(((float) $quantity) * $unitAmount),
                'source_type' => isset($item['source_type']) && $item['source_type'] !== '' ? (string) $item['source_type'] : null,
                'source_id' => isset($item['source_id']) && $item['source_id'] !== '' ? (int) $item['source_id'] : null,
            ];
        }

        return $normalized;
    }
}
