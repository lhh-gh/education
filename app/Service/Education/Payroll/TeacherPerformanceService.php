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

use App\Model\Education\Payroll\EducationTeacherPerformanceMetric;
use App\Repository\Education\Payroll\TeacherPerformanceRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class TeacherPerformanceService
{
    public function __construct(
        private readonly TeacherPerformanceRepository $repository
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
     * @param array<string, mixed> $filters
     * @return array{teacher_count: int, lesson_count: int, salary_amount_cents: int, dispute_count: int}
     */
    public function summary(array $filters, EducationUserContext $context): array
    {
        $query = EducationTeacherPerformanceMetric::query();
        if (! $context->platformAccess) {
            $context->tenantId === null ? $query->whereRaw('1 = 0') : $query->where('tenant_id', $context->tenantId);
        }
        foreach (['metric_month', 'campus_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        return [
            'teacher_count' => (int) (clone $query)->distinct()->count('teacher_id'),
            'lesson_count' => (int) (clone $query)->sum('lesson_count'),
            'salary_amount_cents' => (int) (clone $query)->sum('salary_amount_cents'),
            'dispute_count' => (int) (clone $query)->sum('dispute_count'),
        ];
    }
}
