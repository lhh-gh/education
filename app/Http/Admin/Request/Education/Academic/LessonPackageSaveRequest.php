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
use App\Schema\Education\Academic\LessonPackageSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: LessonPackageSchema::class,
    only: ['tenant_id', 'campus_id', 'course_id', 'code', 'name', 'lesson_units', 'bonus_units', 'list_price', 'sale_price', 'validity_days', 'status', 'sort_order', 'remark']
)]
class LessonPackageSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'required|integer|min:1',
            'course_id' => 'required|integer|min:1',
            'code' => 'required|string|max:64',
            'name' => 'required|string|max:120',
            'lesson_units' => 'required|numeric|min:0|max:999999.99',
            'bonus_units' => 'required|numeric|min:0|max:999999.99',
            'list_price' => 'required|numeric|min:0|max:9999999999.99',
            'sale_price' => 'required|numeric|min:0|max:9999999999.99',
            'validity_days' => 'sometimes|nullable|integer|between:1,3650',
            'status' => 'required|in:enabled,disabled',
            'sort_order' => 'sometimes|nullable|integer|between:-9999,9999',
            'remark' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'campus_id.required' => 'campus_id is required',
            'course_id.required' => 'course_id is required',
            'code.required' => 'code is required',
            'name.required' => 'name is required',
            'lesson_units.required' => 'lesson_units is required',
            'bonus_units.required' => 'bonus_units is required',
            'status.in' => 'status has an invalid value',
        ];
    }
}
