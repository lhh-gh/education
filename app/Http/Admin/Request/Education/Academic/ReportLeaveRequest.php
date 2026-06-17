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

final class ReportLeaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'pageSize' => ['required', 'integer', 'between:1,100'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'class_id' => ['nullable', 'integer', 'min:1'],
            'teacher_id' => ['nullable', 'integer', 'min:1'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'source' => ['nullable', 'in:staff,guardian,teacher'],
            'leave_type' => ['nullable', 'in:sick,personal,school,other'],
            'status' => ['nullable', 'in:pending,approved,rejected,cancelled,makeup_scheduled,closed'],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.required' => 'page is required',
            'pageSize.between' => 'pageSize must be between 1 and 100',
            'campus_id.integer' => 'campus_id must be an integer',
            'start_at.required' => 'start_at is required',
            'start_at.date_format' => 'start_at must use Y-m-d H:i:s',
            'end_at.required' => 'end_at is required',
            'end_at.after' => 'end_at must be after start_at',
            'status.in' => 'status has an invalid value',
        ];
    }
}
