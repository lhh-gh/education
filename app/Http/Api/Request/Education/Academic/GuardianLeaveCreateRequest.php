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

final class GuardianLeaveCreateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'lesson_student_id' => ['required', 'integer', 'min:1'],
            'leave_type' => ['required', 'in:sick,personal,school,other'],
            'reason' => ['required', 'string', 'max:500'],
            'makeup_required' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'lesson_student_id.required' => 'lesson_student_id is required',
            'leave_type.in' => 'leave_type has an invalid value',
            'reason.required' => 'reason is required',
            'reason.max' => 'reason must not exceed 500 characters',
        ];
    }
}
