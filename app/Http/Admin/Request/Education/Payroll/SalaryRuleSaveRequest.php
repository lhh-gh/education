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

namespace App\Http\Admin\Request\Education\Payroll;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class SalaryRuleSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => ['nullable', 'integer', 'min:1'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'rule_code' => ['required', 'string', 'max:64'],
            'rule_name' => ['required', 'string', 'max:120'],
            'campus_scope_json' => ['nullable', 'array'],
            'teacher_grade' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'in:enabled,disabled'],
            'effective_start' => ['required', 'date_format:Y-m-d'],
            'effective_end' => ['nullable', 'date_format:Y-m-d'],
            'priority' => ['nullable', 'integer'],
            'remark' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'string', 'max:40'],
            'items.*.workload_type' => ['nullable', 'string', 'max:40'],
            'items.*.course_id' => ['nullable', 'integer', 'min:1'],
            'items.*.class_type' => ['nullable', 'string', 'max:40'],
            'items.*.calculation_method' => ['required', 'string', 'max:40'],
            'items.*.unit_amount_cents' => ['required', 'integer', 'min:0'],
            'items.*.rate' => ['nullable', 'numeric'],
            'items.*.condition_json' => ['nullable', 'array'],
            'items.*.sort_order' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return ['rule_code.required' => 'rule_code is required'];
    }
}
