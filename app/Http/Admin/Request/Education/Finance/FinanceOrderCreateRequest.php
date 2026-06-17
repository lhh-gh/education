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

namespace App\Http\Admin\Request\Education\Finance;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class FinanceOrderCreateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => ['nullable', 'integer', 'min:1'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'enrollment_id' => ['required_without:lesson_package_id', 'integer', 'min:1'],
            'order_type' => ['nullable', 'string', 'max:40'],
            'student_id' => ['required', 'integer', 'min:1'],
            'guardian_id' => ['nullable', 'integer', 'min:1'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'lesson_package_id' => ['nullable', 'integer', 'min:1'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'string', 'max:40'],
            'items.*.item_name' => ['required', 'string', 'max:160'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_amount_cents' => ['required', 'integer', 'min:1'],
            'items.*.source_type' => ['nullable', 'string', 'max:40'],
            'items.*.source_id' => ['nullable', 'integer', 'min:1'],
            'discount_amount_cents' => ['nullable', 'integer', 'min:0'],
            'due_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'remark' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'enrollment_id.required_without' => 'enrollment_id is required',
            'student_id.required' => 'student_id is required',
            'items.required' => 'items is required',
            'items.*.unit_amount_cents.integer' => 'unit_amount_cents must be a positive integer',
        ];
    }
}
