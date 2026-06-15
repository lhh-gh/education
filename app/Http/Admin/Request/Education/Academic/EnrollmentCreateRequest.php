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

class EnrollmentCreateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'required|integer|min:1',
            'student_id' => 'required|integer|min:1',
            'course_id' => 'required|integer|min:1',
            'lesson_package_id' => 'required|integer|min:1',
            'deal_amount' => 'sometimes|nullable|numeric|min:0|max:9999999999.99',
            'enrolled_at' => 'sometimes|nullable|date_format:Y-m-d H:i:s',
            'remark' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'campus_id.required' => 'campus_id is required',
            'student_id.required' => 'student_id is required',
            'course_id.required' => 'course_id is required',
            'lesson_package_id.required' => 'lesson_package_id is required',
        ];
    }
}
