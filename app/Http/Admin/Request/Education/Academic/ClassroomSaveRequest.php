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
use App\Schema\Education\Academic\ClassroomSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: ClassroomSchema::class,
    only: ['tenant_id', 'campus_id', 'code', 'name', 'capacity', 'location', 'equipment', 'status', 'sort_order', 'remark']
)]
class ClassroomSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'required|integer|min:1',
            'code' => 'required|string|max:64',
            'name' => 'required|string|max:120',
            'capacity' => 'sometimes|nullable|integer|min:0|max:9999',
            'location' => 'sometimes|nullable|string|max:120',
            'equipment' => 'sometimes|nullable|array',
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
            'status.in' => 'status must be one of enabled, disabled',
        ];
    }
}
