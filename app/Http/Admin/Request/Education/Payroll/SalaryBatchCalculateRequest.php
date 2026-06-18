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

final class SalaryBatchCalculateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'salary_month' => ['required', 'date_format:Y-m'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return ['salary_month.date_format' => 'salary_month must be YYYY-MM'];
    }
}
