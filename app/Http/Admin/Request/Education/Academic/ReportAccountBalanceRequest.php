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

namespace App\Http\Admin\Request\Education\Academic;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class ReportAccountBalanceRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'pageSize' => ['required', 'integer', 'between:1,100'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'student_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:active,frozen,closed'],
            'balance_level' => ['nullable', 'in:zero,low,normal,expired,expiring_soon'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.required' => 'page is required',
            'pageSize.between' => 'pageSize must be between 1 and 100',
            'campus_id.integer' => 'campus_id must be an integer',
            'balance_level.in' => 'balance_level has an invalid value',
            'status.in' => 'status has an invalid value',
        ];
    }
}
