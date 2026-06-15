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
use App\Schema\Education\Academic\StudentSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: StudentSchema::class,
    only: ['tenant_id', 'campus_id', 'student_no', 'name', 'gender', 'birthday', 'mobile', 'school', 'grade', 'source', 'avatar', 'enrolled_at', 'status', 'remark']
)]
class StudentSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'required|integer|min:1',
            'student_no' => 'required|string|max:64',
            'name' => 'required|string|max:120',
            'gender' => 'required|in:male,female,unknown',
            'birthday' => 'sometimes|nullable|date_format:Y-m-d',
            'mobile' => 'sometimes|nullable|string|max:30',
            'school' => 'sometimes|nullable|string|max:120',
            'grade' => 'sometimes|nullable|string|max:60',
            'source' => 'sometimes|nullable|string|max:80',
            'avatar' => 'sometimes|nullable|string|max:255',
            'enrolled_at' => 'sometimes|nullable|date_format:Y-m-d',
            'status' => 'required|in:enabled,disabled',
            'remark' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'campus_id.required' => 'campus_id is required',
            'student_no.required' => 'student_no is required',
            'gender.in' => 'gender must be one of male, female, unknown',
            'status.in' => 'status must be one of enabled, disabled',
        ];
    }
}
