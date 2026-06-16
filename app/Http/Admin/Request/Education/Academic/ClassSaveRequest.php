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
use App\Schema\Education\Academic\ClassSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(schema: ClassSchema::class)]
class ClassSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'required|integer|min:1',
            'course_id' => 'required|integer|min:1',
            'main_teacher_id' => 'sometimes|nullable|integer|min:1',
            'classroom_id' => 'sometimes|nullable|integer|min:1',
            'code' => 'required|string|max:64',
            'name' => 'required|string|max:120',
            'class_type' => 'sometimes|in:group,one_to_one',
            'max_students' => 'sometimes|integer|min:0',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date',
            'lesson_units' => 'required|numeric|min:0.01',
            'status' => 'sometimes|in:enabled,disabled',
            'schedule_note' => 'sometimes|nullable|string|max:500',
            'remark' => 'sometimes|nullable|string|max:500',
        ];
    }
}
