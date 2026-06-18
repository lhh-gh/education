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
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Finance\EducationFinanceOrder;
use App\Model\Education\Finance\EducationReceipt;
use App\Model\Education\Finance\EducationRefundRequest;
use App\Service\Education\Academic\GuardianMobileContextResolver;
use App\Service\Education\Foundation\EducationUserContext;

final class GuardianFinanceService
{
    public function __construct(
        private readonly GuardianMobileContextResolver $contextResolver
    ) {}

    /**
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function orders(array $params, EducationUserContext $context): array
    {
        $studentId = (int) ($params['student_id'] ?? 0);
        $this->assertBoundStudent($studentId, $context);
        $query = EducationFinanceOrder::query()->where('tenant_id', $context->tenantId)->where('student_id', $studentId);
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (string) $params['status']);
        }

        return $this->page($query->orderByDesc('id'), $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function receipts(array $params, EducationUserContext $context): array
    {
        $studentId = (int) ($params['student_id'] ?? 0);
        $this->assertBoundStudent($studentId, $context);
        $query = EducationReceipt::query()->where('tenant_id', $context->tenantId)->where('student_id', $studentId);
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (string) $params['status']);
        }

        return $this->page($query->orderByDesc('id'), $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function refunds(array $params, EducationUserContext $context): array
    {
        $studentId = (int) ($params['student_id'] ?? 0);
        $this->assertBoundStudent($studentId, $context);
        $orderIds = EducationFinanceOrder::query()->where('tenant_id', $context->tenantId)->where('student_id', $studentId)->pluck('id')->all();
        $query = EducationRefundRequest::query()->where('tenant_id', $context->tenantId)->whereIn('order_id', $orderIds);
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (string) $params['status']);
        }

        return $this->page($query->orderByDesc('id'), $params);
    }

    private function assertBoundStudent(int $studentId, EducationUserContext $context): void
    {
        $guardian = $this->contextResolver->resolveGuardian($context);
        $exists = EducationStudentGuardian::query()
            ->where('tenant_id', $context->tenantId)
            ->where('guardian_id', $guardian->id)
            ->where('student_id', $studentId)
            ->exists();
        if (! $exists) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'student is not bound to current guardian', ['student_id' => $studentId]);
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    private function page(mixed $query, array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->forPage($page, $pageSize)->get()->map(static fn ($row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
