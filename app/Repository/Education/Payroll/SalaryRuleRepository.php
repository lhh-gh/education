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

namespace App\Repository\Education\Payroll;

use App\Model\Education\Operations\EducationTeacherWorkloadRecord;
use App\Model\Education\Payroll\EducationTeacherSalaryRule;
use App\Model\Education\Payroll\EducationTeacherSalaryRuleItem;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class SalaryRuleRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($filters, $context);
        foreach (['status', 'teacher_grade'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        if (isset($filters['keyword']) && $filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $query->where(static fn ($query) => $query->where('rule_code', 'like', $keyword)->orWhere('rule_name', 'like', $keyword));
        }

        return $this->paginate($query->orderByDesc('priority')->orderByDesc('id'), $filters);
    }

    public function findByCode(int $tenantId, string $code): ?EducationTeacherSalaryRule
    {
        $rule = EducationTeacherSalaryRule::query()->where('tenant_id', $tenantId)->where('rule_code', $code)->first();

        return $rule instanceof EducationTeacherSalaryRule ? $rule : null;
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationTeacherSalaryRule
    {
        $rule = $this->scopedQuery([], $context)->whereKey($id)->first();

        return $rule instanceof EducationTeacherSalaryRule ? $rule : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationTeacherSalaryRule
    {
        return EducationTeacherSalaryRule::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createItem(array $data): EducationTeacherSalaryRuleItem
    {
        return EducationTeacherSalaryRuleItem::query()->create($data);
    }

    /**
     * @return null|array{rule: EducationTeacherSalaryRule, item: EducationTeacherSalaryRuleItem}
     */
    public function matchItem(EducationTeacherWorkloadRecord $workload, string $salaryMonth, EducationUserContext $context): ?array
    {
        $monthDate = $salaryMonth . '-01';
        $ruleQuery = $this->scopedQuery(['campus_id' => (int) $workload->campus_id], $context)
            ->where('status', 'enabled')
            ->where('effective_start', '<=', $monthDate)
            ->where(static fn ($query) => $query->whereNull('effective_end')->orWhere('effective_end', '>=', $monthDate))
            ->orderByDesc('priority')
            ->orderByDesc('id');
        /** @var iterable<EducationTeacherSalaryRule> $rules */
        $rules = $ruleQuery->get();
        foreach ($rules as $rule) {
            $item = EducationTeacherSalaryRuleItem::query()
                ->where('tenant_id', $rule->tenant_id)
                ->where('rule_id', $rule->id)
                ->where('item_type', 'workload')
                ->where(static fn ($query) => $query->whereNull('workload_type')->orWhere('workload_type', $workload->workload_type))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();
            if ($item instanceof EducationTeacherSalaryRuleItem) {
                return ['rule' => $rule, 'item' => $item];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function scopedQuery(array $filters, EducationUserContext $context): mixed
    {
        return (new EducationScopeQuery())->applyTenantCampus(EducationTeacherSalaryRule::query(), $filters, $context);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    private function paginate(mixed $query, array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->forPage($page, $pageSize)->get()->map(static fn (EducationTeacherSalaryRule $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
