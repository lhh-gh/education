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
use App\Schema\Education\Academic\CourseSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: CourseSchema::class,
    only: ['tenant_id', 'campus_id', 'code', 'name', 'category', 'subject', 'unit_minutes', 'cover_url', 'description', 'status', 'sort_order', 'remark']
)]
class CourseSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'required|integer|min:1',
            'code' => 'required|string|max:64',
            'name' => 'required|string|max:120',
            'category' => 'sometimes|nullable|string|max:80',
            'subject' => 'sometimes|nullable|string|max:80',
            'unit_minutes' => 'required|integer|between:1,1440',
            'cover_url' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:5000',
            'status' => 'required|in:enabled,disabled',
            'sort_order' => 'sometimes|nullable|integer|between:-9999,9999',
            'remark' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'campus_id.required' => 'campus_id is required',
            'code.required' => 'code is required',
            'name.required' => 'name is required',
            'unit_minutes.required' => 'unit_minutes is required',
            'status.in' => 'status has an invalid value',
        ];
    }
}
