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

class LeaveRequestCreateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'lesson_student_id' => ['required', 'integer', 'min:1'],
            'source' => ['required', 'in:staff,guardian,teacher'],
            'leave_type' => ['required', 'in:sick,personal,school,other'],
            'guardian_id' => ['nullable', 'integer', 'min:1'],
            'teacher_id' => ['nullable', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
            'makeup_required' => ['nullable', 'boolean'],
            'remark' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'lesson_student_id.required' => 'lesson_student_id is required',
            'reason.required' => 'reason is required',
        ];
    }
}
