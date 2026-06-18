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
use App\Schema\Education\Academic\TeacherSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: TeacherSchema::class,
    only: ['tenant_id', 'campus_id', 'user_profile_id', 'teacher_no', 'name', 'mobile', 'gender', 'birthday', 'title', 'hire_date', 'avatar', 'introduction', 'status', 'remark']
)]
class TeacherSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'required|integer|min:1',
            'user_profile_id' => 'sometimes|nullable|integer|min:1',
            'teacher_no' => 'required|string|max:64',
            'name' => 'required|string|max:120',
            'mobile' => 'sometimes|nullable|string|max:30',
            'gender' => 'required|in:male,female,unknown',
            'birthday' => 'sometimes|nullable|date_format:Y-m-d',
            'title' => 'sometimes|nullable|string|max:80',
            'hire_date' => 'sometimes|nullable|date_format:Y-m-d',
            'avatar' => 'sometimes|nullable|string|max:255',
            'introduction' => 'sometimes|nullable|string|max:5000',
            'status' => 'required|in:enabled,disabled',
            'remark' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'campus_id.required' => 'campus_id is required',
            'teacher_no.required' => 'teacher_no is required',
            'gender.in' => 'gender must be one of male, female, unknown',
            'status.in' => 'status must be one of enabled, disabled',
        ];
    }
}
