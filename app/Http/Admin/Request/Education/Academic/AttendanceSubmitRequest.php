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

class AttendanceSubmitRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'submitted_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.lesson_student_id' => ['required', 'integer', 'min:1'],
            'records.*.attendance_status' => ['required', 'in:present,late,absent,leave'],
            'records.*.consume_policy' => ['required', 'in:consume,no_consume'],
            'records.*.consumed_units' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'records.*.remark' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'records.required' => 'records is required',
            'records.*.lesson_student_id.required' => 'lesson_student_id is required',
            'records.*.attendance_status.in' => 'attendance_status has an invalid value',
            'records.*.consume_policy.in' => 'consume_policy has an invalid value',
            'records.*.consumed_units.required' => 'consumed_units is required',
        ];
    }
}
