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

namespace App\Http\Api\Request\Education\Academic;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class TeacherAttendanceSaveRequest extends FormRequest
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
            'records.*.consumed_units' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'records.*.remark' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'records.required' => 'records is required',
            'records.array' => 'records must be an array',
            'records.min' => 'at least one attendance row is required',
            'records.*.lesson_student_id.required' => 'lesson_student_id is required',
            'records.*.attendance_status.in' => 'attendance_status has an invalid value',
            'records.*.consume_policy.in' => 'consume_policy has an invalid value',
            'records.*.consumed_units.numeric' => 'consumed_units must be numeric',
            'records.*.consumed_units.min' => 'consumed_units must be at least 0',
            'records.*.remark.max' => 'remark must not exceed 300 characters',
        ];
    }
}
