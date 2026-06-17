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

use App\Model\Education\Payroll\EducationTeacherSalarySlip;
use App\Model\Education\Payroll\EducationTeacherWorkloadDispute;
use App\Service\Education\Academic\TeacherMobileContextResolver;
use App\Service\Education\Foundation\EducationUserContext;

final class TeacherPayrollService
{
    public function __construct(
        private readonly TeacherMobileContextResolver $resolver,
        private readonly WorkloadDisputeService $disputeService
    ) {}

    /**
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function slips(array $params, EducationUserContext $context): array
    {
        $teacher = $this->resolver->resolveTeacher($context);
        $query = EducationTeacherSalarySlip::query()
            ->where('tenant_id', $context->tenantId)
            ->where('teacher_id', $teacher->id);
        foreach (['salary_month', 'status'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }

        return $this->paginate($query->orderByDesc('id'), $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function disputes(array $params, EducationUserContext $context): array
    {
        $teacher = $this->resolver->resolveTeacher($context);
        $query = EducationTeacherWorkloadDispute::query()
            ->where('tenant_id', $context->tenantId)
            ->where('teacher_id', $teacher->id);
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }

        return $this->paginate($query->orderByDesc('id'), $params);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{dispute_id: int, status: string}
     */
    public function submitDispute(array $data, EducationUserContext $context): array
    {
        $teacher = $this->resolver->resolveTeacher($context);

        return $this->disputeService->submitForTeacher((int) $teacher->id, $data, $context);
    }

    /**
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    private function paginate(mixed $query, array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->forPage($page, $pageSize)->get()->map(static fn (mixed $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
