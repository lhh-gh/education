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
use App\Model\Education\Payroll\EducationTeacherSalaryRule;
use App\Model\Enums\Education\Payroll\SalaryRuleStatus;
use App\Repository\Education\Payroll\SalaryRuleRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;

final class SalaryRuleService
{
    public function __construct(
        private readonly SalaryRuleRepository $repository
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
     * @return array{rule_id: int, status: string}
     */
    public function save(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $tenantId = (int) $context->tenantId;
            $ruleCode = (string) ($data['rule_code'] ?? '');
            if ($ruleCode === '') {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'rule_code is required', ['field' => 'rule_code']);
            }
            if ($this->repository->findByCode($tenantId, $ruleCode) instanceof EducationTeacherSalaryRule) {
                throw new BusinessException(ResultCode::CONFLICT, 'salary rule code already exists', ['rule_code' => $ruleCode]);
            }
            $rule = $this->repository->create([
                'tenant_id' => $tenantId,
                'campus_id' => isset($data['campus_id']) && $data['campus_id'] !== '' ? (int) $data['campus_id'] : $context->currentCampusId,
                'rule_code' => $ruleCode,
                'rule_name' => (string) ($data['rule_name'] ?? $ruleCode),
                'campus_scope_json' => \is_array($data['campus_scope_json'] ?? null) ? $data['campus_scope_json'] : null,
                'teacher_grade' => isset($data['teacher_grade']) && $data['teacher_grade'] !== '' ? (string) $data['teacher_grade'] : null,
                'status' => (string) ($data['status'] ?? SalaryRuleStatus::Enabled->value),
                'effective_start' => (string) ($data['effective_start'] ?? date('Y-m-d')),
                'effective_end' => isset($data['effective_end']) && $data['effective_end'] !== '' ? (string) $data['effective_end'] : null,
                'priority' => (int) ($data['priority'] ?? 0),
                'remark' => $data['remark'] ?? null,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            foreach ($this->items($data['items'] ?? []) as $item) {
                $this->repository->createItem([
                    'tenant_id' => $tenantId,
                    'campus_id' => $rule->campus_id === null ? null : (int) $rule->campus_id,
                    'rule_id' => (int) $rule->id,
                    'item_type' => $item['item_type'],
                    'workload_type' => $item['workload_type'],
                    'course_id' => $item['course_id'],
                    'class_type' => $item['class_type'],
                    'calculation_method' => $item['calculation_method'],
                    'unit_amount_cents' => $item['unit_amount_cents'],
                    'rate' => $item['rate'],
                    'condition_json' => $item['condition_json'],
                    'sort_order' => $item['sort_order'],
                    'created_by' => $context->userId,
                    'updated_by' => $context->userId,
                ]);
            }

            return ['rule_id' => (int) $rule->id, 'status' => (string) $rule->status];
        });
    }

    /**
     * @return array{rule_id: int, rule_code: string, rule_item_id: int, unit_amount_cents: int, calculation_method: string}
     */
    public function matchForWorkload(EducationTeacherWorkloadRecord $workload, string $salaryMonth, EducationUserContext $context): array
    {
        $matched = $this->repository->matchItem($workload, $salaryMonth, $context);
        if ($matched === null) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'salary rule item not found for workload', ['workload_id' => (int) $workload->id]);
        }

        return [
            'rule_id' => (int) $matched['rule']->id,
            'rule_code' => (string) $matched['rule']->rule_code,
            'rule_item_id' => (int) $matched['item']->id,
            'unit_amount_cents' => (int) $matched['item']->unit_amount_cents,
            'calculation_method' => (string) $matched['item']->calculation_method,
        ];
    }

    /**
     * @return array<int, array{item_type: string, workload_type: ?string, course_id: ?int, class_type: ?string, calculation_method: string, unit_amount_cents: int, rate: mixed, condition_json: mixed, sort_order: int}>
     */
    private function items(mixed $items): array
    {
        if (! \is_array($items) || $items === []) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'salary rule items are required');
        }
        $normalized = [];
        foreach ($items as $index => $item) {
            if (! \is_array($item)) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'salary rule item is invalid');
            }
            $unitAmount = (int) ($item['unit_amount_cents'] ?? 0);
            if ($unitAmount < 0) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'unit_amount_cents is invalid', ['field' => 'unit_amount_cents']);
            }
            $normalized[] = [
                'item_type' => (string) ($item['item_type'] ?? 'workload'),
                'workload_type' => isset($item['workload_type']) && $item['workload_type'] !== '' ? (string) $item['workload_type'] : null,
                'course_id' => isset($item['course_id']) && $item['course_id'] !== '' ? (int) $item['course_id'] : null,
                'class_type' => isset($item['class_type']) && $item['class_type'] !== '' ? (string) $item['class_type'] : null,
                'calculation_method' => (string) ($item['calculation_method'] ?? 'per_credit'),
                'unit_amount_cents' => $unitAmount,
                'rate' => $item['rate'] ?? null,
                'condition_json' => \is_array($item['condition_json'] ?? null) ? $item['condition_json'] : null,
                'sort_order' => (int) ($item['sort_order'] ?? $index),
            ];
        }

        return $normalized;
    }
}
